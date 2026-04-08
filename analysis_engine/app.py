from flask import Flask, request, jsonify
import google.generativeai as genai
from groq import Groq
import os
import base64
from PIL import Image
import json
import io
import time
import re
from dotenv import load_dotenv

# Local AI Libraries
try:
    import torch
    from transformers import pipeline
    HAS_CORE = True
    local_classifier = pipeline("image-classification", model="wambugu71/crop_leaf_diseases_vit")
except Exception as e:
    print(f"Core Engine Init Error: {e}")
    HAS_CORE = False

# Environment setup
env_path = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '.env'))
load_dotenv(env_path, override=True)

app = Flask(__name__)

# Engine configuration
ALPHA_KEY = os.getenv("ENGINE_PROVIDER_ALPHA_KEY")
BETA_KEY = os.getenv("ENGINE_PROVIDER_BETA_KEY")
ALPHA_MODEL = "models/gemini-2.5-flash"
BETA_MODEL = "meta-llama/llama-4-scout-17b-16e-instruct" 

def clean_output(text: str) -> str:
    """Strip redundant AI notes from the response."""
    text = re.sub(r'\(.*?(translation|already|needed).*?\)', '', text, flags=re.IGNORECASE)
    text = re.sub(r'^(translation|result|output):\s*', '', text, flags=re.IGNORECASE)
    return text.strip()

# Initialize providers
HAS_ALPHA = False
if ALPHA_KEY:
    try:
        genai.configure(api_key=ALPHA_KEY)
        HAS_ALPHA = True
    except Exception as e:
        pass

HAS_BETA = False
if BETA_KEY:
    try:
        beta_client = Groq(api_key=BETA_KEY)
        HAS_BETA = True
    except Exception as e:
        pass

CORE_ANALYSIS_INSTRUCTIONS = """
You are a Professional Agricultural Plant Pathologist specializing in Sri Lankan and tropical crops.
Analyze the provided image(s) and return ONLY a JSON response:
{
  "disease": "Consolidated Disease Name or Healthy",
  "confidence": 0.95,
  "severity": "Low/Medium/High",
  "spread_risk": "Low/Medium/High",
  "treatment": "Detailed step-by-step treatment protocol."
}
If multiple images are provided, they are from the SAME plant. Use all of them to make a more accurate consolidated diagnosis.
Support as many agricultural crops as possible (e.g., Vegetables, Fruits, Grains, Cash Crops, Potato, Onion). 
If the image is not related to a plant or agriculture, return "Invalid Image".
Return only raw JSON without any notes or explanations.
"""

def predict_primary(imgs: list, prompt: str) -> str:
    model = genai.GenerativeModel(ALPHA_MODEL)
    contents = [prompt] + imgs
    response = model.generate_content(contents, request_options={"timeout": 60})
    return response.text

def predict_secondary(image_bytes_list: list, prompt: str) -> str:
    base64_image = base64.b64encode(image_bytes_list[0]).decode('utf-8')
    completion = beta_client.chat.completions.create(
        model=BETA_MODEL,
        messages=[
            {
                "role": "user",
                "content": [
                    {"type": "text", "text": prompt},
                    {
                        "type": "image_url",
                        "image_url": {"url": f"data:image/jpeg;base64,{base64_image}"}
                    },
                ],
            }
        ],
        temperature=0.1,
        response_format={"type": "json_object"},
        timeout=60
    )
    return completion.choices[0].message.content

def predict_local(pil_imgs, lang='en'):
    if not HAS_CORE:
        return json.dumps({"disease": "Local Engine Offline", "confidence": 0, "treatment": "Local processing unavailable."})
        
    results = local_classifier(pil_imgs[0])
    top_result = results[0]
    label = top_result['label'].replace('___', ': ').replace('__', ' ').replace('_', ' ').title()
    conf = float(top_result['score'])
    
    return json.dumps({
        "disease": f"{label} (Local Mode)",
        "treatment": "Analysis performed via local processing node. For more detailed protocols, ensure cloud synchronization is active.",
        "confidence": conf,
        "engine_tier": "Core"
    })

@app.route('/health', methods=['GET'])
def health():
    return jsonify({
        "status": "online",
        "engine_tiers": {
            "alpha": "ACTIVE" if HAS_ALPHA else "INACTIVE",
            "beta": "ACTIVE" if HAS_BETA else "INACTIVE",
            "core": "ACTIVE" if HAS_CORE else "INACTIVE"
        }
    })

@app.route('/translate', methods=['POST'])
def translate():
    """Translate a given text to a target language using AI."""
    data = request.get_json()
    text = data.get('text')
    target_lang = data.get('lang', 'en')

    if not text:
        return jsonify({"error": "text is required"}), 400

    lang_map = {
        'si': 'Sinhala (සිංහල)',
        'ta': 'Tamil (தமிழ்)',
        'en': 'English'
    }
    
    target_name = lang_map.get(target_lang, 'English')
    
    # Strict prompt to ensure high-quality translation
    prompt = f"Translate the following agricultural text to {target_name}. "
    if target_lang == 'si':
        prompt += "Use only pure Sinhala words. Avoid mixing other languages. "
    prompt += f"Return ONLY the translated text without any explanations or notes:\n\n{text}"

    try:
        content = ""
        # Try Beta layer first
        if HAS_BETA:
            try:
                completion = beta_client.chat.completions.create(
                    model=BETA_MODEL,
                    messages=[{"role": "user", "content": prompt}],
                    temperature=0.1,
                    timeout=30
                )
                content = clean_output(completion.choices[0].message.content.strip())
            except: pass

        # Fallback to Alpha layer
        if not content and HAS_ALPHA:
            try:
                model = genai.GenerativeModel(ALPHA_MODEL)
                response = model.generate_content(prompt)
                content = clean_output(response.text.strip())
            except: pass

        if not content:
            return jsonify({"translated": text}) # Fallback to original

        return jsonify({"translated": content})

    except Exception as e:
        return jsonify({"translated": text, "error": str(e)})

@app.route('/predict', methods=['POST'])
def predict():
    start_time = time.time()
    image_files = request.files.getlist('images[]')
    lang = request.args.get('lang', 'en')
    
    if not image_files: return jsonify({"error": "No images"}), 400
    
    # Always use English for predictions to keep database records consistent.
    # Translation is handled dynamically by the application layer.
    localized_prompt = CORE_ANALYSIS_INSTRUCTIONS
    
    image_bytes_list = []
    pil_images = []
    for img_file in image_files:
        b = img_file.read()
        image_bytes_list.append(b)
        try:
            pil_images.append(Image.open(io.BytesIO(b)).convert("RGB"))
        except: continue

    if not pil_images: return jsonify({"error": "Invalid images"}), 400

    try:
        content = ""
        tier = ""
        if HAS_ALPHA:
            try:
                content = predict_primary(pil_images, localized_prompt)
                tier = "Alpha"
            except: pass

        if not content and HAS_BETA:
            try:
                content = predict_secondary(image_bytes_list, localized_prompt)
                tier = "Beta"
            except: pass

        if not content and HAS_CORE:
            return predict_local(pil_images, lang)

        if not content:
            return jsonify({"disease": "Service Unavailable", "confidence": 0, "treatment": "All AI tiers are currently offline."})

        if "```json" in content:
            content = content.split("```json")[1].split("```")[0].strip()
        elif "```" in content:
            content = content.split("```")[1].split("```")[0].strip()
            
        prediction = json.loads(content)
        prediction['disease'] = clean_output(prediction['disease'])
        prediction['treatment'] = clean_output(prediction['treatment'])
        prediction['engine_tier'] = tier
        return jsonify(prediction)

    except Exception as e:
        return jsonify({"disease": "Analysis Error", "confidence": 0, "treatment": str(e)}), 500

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5056, debug=False)
