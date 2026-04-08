from flask import Flask, request, jsonify
import google.generativeai as genai
from groq import Groq
import os
import base64
from PIL import Image
import json
import io
import time
from dotenv import load_dotenv

# NEW: Local AI Libraries
try:
    import torch
    from transformers import pipeline
    HAS_CORE = True
    local_classifier = pipeline("image-classification", model="wambugu71/crop_leaf_diseases_vit")
except Exception as e:
    print(f"Core Engine Init Error: {e}")
    HAS_CORE = False

# Load environment variables from parent directory
env_path = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '.env'))
load_dotenv(env_path, override=True)

app = Flask(__name__)

# --- ANALYSIS ENGINE CONFIGURATION ---
ALPHA_KEY = os.getenv("ENGINE_PROVIDER_ALPHA_KEY")
BETA_KEY = os.getenv("ENGINE_PROVIDER_BETA_KEY")
ALPHA_MODEL = "models/gemini-2.5-flash"
BETA_MODEL = "meta-llama/llama-4-scout-17b-16e-instruct" 

# Setup Engines
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

@app.route('/predict', methods=['POST'])
def predict():
    start_time = time.time()
    image_files = request.files.getlist('images[]')
    lang = request.args.get('lang', 'en')
    
    if not image_files: return jsonify({"error": "No images"}), 400
    
    localized_prompt = CORE_ANALYSIS_INSTRUCTIONS
    if lang == 'si':
        localized_prompt += "\nRespond in Sinhala for disease/treatment. Chemicals in English."
    elif lang == 'ta':
        localized_prompt += "\nRespond in Tamil for disease/treatment. Chemicals in English."
    
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
        prediction['engine_tier'] = tier
        return jsonify(prediction)

    except Exception as e:
        return jsonify({"disease": "Analysis Error", "confidence": 0, "treatment": str(e)}), 500

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5056, debug=False)
