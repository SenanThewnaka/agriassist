import logging
from flask import Flask, request, jsonify, Response
import google.generativeai as genai
from groq import Groq
import os
import base64
from PIL import Image
import json
import io
import time
import re
from typing import List, Dict, Any, Optional, Union
from dotenv import load_dotenv

# Configure professional logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

# Local AI Libraries
try:
    import torch
    from transformers import pipeline
    HAS_CORE = True
    local_classifier = pipeline("image-classification", model="wambugu71/crop_leaf_diseases_vit")
    logger.info("Core Engine (Local) initialized successfully.")
except Exception as e:
    logger.error(f"Core Engine Init Error: {e}")
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
    """
    Strips redundant AI notes and prefixes from the response text.
    
    Args:
        text (str): The raw string from the AI model.
        
    Returns:
        str: Cleaned text.
    """
    text = re.sub(r'\(.*?(translation|already|needed).*?\)', '', text, flags=re.IGNORECASE)
    text = re.sub(r'^(translation|result|output):\s*', '', text, flags=re.IGNORECASE)
    return text.strip()

# Initialize providers
HAS_ALPHA = False
if ALPHA_KEY:
    try:
        genai.configure(api_key=ALPHA_KEY)
        HAS_ALPHA = True
        logger.info("Alpha Provider (Gemini) configured.")
    except Exception as e:
        logger.error(f"Alpha Provider configuration failed: {e}")

HAS_BETA = False
if BETA_KEY:
    try:
        beta_client = Groq(api_key=BETA_KEY)
        HAS_BETA = True
        logger.info("Beta Provider (Llama/Groq) configured.")
    except Exception as e:
        logger.error(f"Beta Provider configuration failed: {e}")

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

def predict_primary(imgs: List[Image.Image], prompt: str) -> str:
    """
    Calls the primary AI tier (Alpha) for image analysis.
    """
    model = genai.GenerativeModel(ALPHA_MODEL)
    contents = [prompt] + imgs
    response = model.generate_content(contents, request_options={"timeout": 60})
    return response.text

def predict_secondary(image_bytes_list: List[bytes], prompt: str) -> str:
    """
    Calls the secondary AI tier (Beta) using base64 image encoding.
    """
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

def predict_local(pil_imgs: List[Image.Image], lang: str = 'en') -> str:
    """
    Falls back to local on-premise classification model if cloud tiers fail.
    """
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
def health() -> Response:
    """System health check endpoint."""
    return jsonify({
        "status": "online",
        "engine_tiers": {
            "alpha": "ACTIVE" if HAS_ALPHA else "INACTIVE",
            "beta": "ACTIVE" if HAS_BETA else "INACTIVE",
            "core": "ACTIVE" if HAS_CORE else "INACTIVE"
        }
    })

@app.route('/translate', methods=['POST'])
def translate() -> Response:
    """
    API endpoint to translate agricultural text using the primary AI tier.
    """
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
            except Exception as e:
                logger.warning(f"Translation Beta failed: {e}")

        # Fallback to Alpha layer
        if not content and HAS_ALPHA:
            try:
                model = genai.GenerativeModel(ALPHA_MODEL)
                response = model.generate_content(prompt)
                content = clean_output(response.text.strip())
            except Exception as e:
                logger.error(f"Translation Alpha failed: {e}")

        return jsonify({"translated": content or text})

    except Exception as e:
        logger.error(f"Translation Error: {e}")
        return jsonify({"translated": text, "error": str(e)})

@app.route('/suggest-varieties', methods=['POST'])
def suggest_varieties() -> Response:
    """
    Suggests 3 common seed varieties for a custom crop using AI.
    """
    data = request.get_json()
    crop = data.get('crop')
    lang = data.get('lang', 'en')
    
    if not crop:
        return jsonify({"error": "crop name is required"}), 400

    prompt = f"First, verify if '{crop}' is a real, legal agricultural crop. If it is an illegal drug, a non-plant item, or nonsensical, return ONLY a JSON object: {{\"error\": \"Invalid or illegal crop requested.\"}} and STOP. "
    prompt += f"Otherwise, suggest 3 popular cultivation varieties or seed types for the crop '{crop}' in Sri Lanka. "
    prompt += "For each variety, provide: name (English), name_si (Sinhala), name_ta (Tamil), growth_days (number), price_per_kg_lkr (estimated number), advantages (English), advantages_si (Sinhala), and advantages_ta (Tamil). "
    prompt += "Return ONLY a JSON object with a 'varieties' key containing an array of 3 objects with these keys: name, name_si, name_ta, growth_days, advantages, advantages_si, advantages_ta, and price_per_kg_lkr. "
    prompt += "Ensure all Sinhala and Tamil translations are accurate and professional."

    try:
        content = ""
        # Tier 1
        if HAS_BETA:
            try:
                completion = beta_client.chat.completions.create(
                    model=BETA_MODEL,
                    messages=[{"role": "user", "content": prompt}],
                    temperature=0.3,
                    response_format={"type": "json_object"},
                    timeout=30
                )
                content = completion.choices[0].message.content.strip()
            except Exception as e:
                logger.warning(f"Tier 1 Variety Suggestion failed: {e}")

        # Tier 2
        if not content and HAS_ALPHA:
            try:
                model = genai.GenerativeModel(ALPHA_MODEL)
                response = model.generate_content(prompt)
                content = response.text.strip()
                if "```json" in content:
                    content = content.split("```json")[1].split("```")[0].strip()
            except Exception as e:
                logger.error(f"Tier 2 Variety Suggestion failed: {e}")

        if content:
            parsed = json.loads(content)
            
            # Handle AI-level validation errors
            if isinstance(parsed, dict) and "error" in parsed:
                return jsonify(parsed), 422

            return jsonify(parsed)

        return jsonify({"varieties": [
            {
                "name": "Standard Local", "name_si": "සාමාන්‍ය දේශීය", "name_ta": "சாதாரண உள்ளூர்",
                "growth_days": 90, "price_per_kg_lkr": 500,
                "advantages": "Commonly available, resilient.", "advantages_si": "පහසුවෙන් ලබාගත හැක, ඔරොත්තු දීමේ හැකියාව ඇත.", "advantages_ta": "பொதுவாகக் கிடைக்கும், மீள்தன்மை கொண்டது."
            },
            {
                "name": "Hybrid High-Yield", "name_si": "දෙමුහුන් ඉහළ අස්වැන්නක්", "name_ta": "கலப்பின அதிக மகசூல்",
                "growth_days": 110, "price_per_kg_lkr": 1200,
                "advantages": "High productivity, pest resistant.", "advantages_si": "ඉහළ ඵලදායිතාව, පළිබෝධ ප්‍රතිරෝධී.", "advantages_ta": "அதிக உற்பத்தித்திறன், பூச்சி எதிர்ப்பு."
            },
            {
                "name": "Fast Growing", "name_si": "වේගයෙන් වර්ධනය වන", "name_ta": "வேகமாக வளரும்",
                "growth_days": 65, "price_per_kg_lkr": 800,
                "advantages": "Shortest duration, good for off-season.", "advantages_si": "කෙටිම කාලය, අවාරයට සුදුසුයි.", "advantages_ta": "குறுகிய காலம், பருவமற்ற காலத்திற்கு நல்லது."
            }
        ]})
    except Exception as e:
        logger.error(f"Suggest Varieties Error: {e}")
        return jsonify({"error": str(e)}), 500

@app.route('/generate-plan', methods=['POST'])
def generate_plan() -> Response:
    """
    Generates a full cultivation roadmap JSON for a specific crop/variety.
    """
    data = request.get_json()
    prompt = data.get('prompt')
    
    if not prompt:
        return jsonify({"error": "prompt is required"}), 400

    try:
        content = ""
        # Tier 1 - Fast JSON generation
        if HAS_BETA:
            try:
                # Groq requires the word 'json' in the prompt for json_object format
                groq_prompt = prompt if "json" in prompt.lower() else f"{prompt}\nReturn as json."
                completion = beta_client.chat.completions.create(
                    model=BETA_MODEL,
                    messages=[{"role": "user", "content": groq_prompt}],
                    temperature=0.1,
                    response_format={"type": "json_object"},
                    timeout=60
                )
                content = completion.choices[0].message.content.strip()
            except Exception as e:
                logger.warning(f"Tier 1 Plan Gen failed: {e}")

        # Tier 2 - Fallback
        if not content and HAS_ALPHA:
            try:
                model = genai.GenerativeModel(ALPHA_MODEL)
                response = model.generate_content(prompt)
                content = response.text.strip()
            except Exception as e:
                logger.error(f"Tier 2 Plan Gen failed: {e}")

        if not content:
            return jsonify({"error": "Analysis engines offline"}), 503

        if "```json" in content:
            content = content.split("```json")[1].split("```")[0].strip()
        elif "```" in content:
            content = content.split("```")[1].split("```")[0].strip()

        return jsonify(json.loads(content))

    except Exception as e:
        logger.error(f"Generate Plan Error: {e}")
        return jsonify({"error": str(e)}), 500

@app.route('/predict', methods=['POST'])
def predict() -> Response:
    """
    Main image analysis endpoint for plant disease diagnosis.
    """
    start_time = time.time()
    image_files = request.files.getlist('images[]')
    lang = request.args.get('lang', 'en')
    context_data = request.form.get('context')
    
    if not image_files: return jsonify({"error": "No images"}), 400
    
    localized_prompt = CORE_ANALYSIS_INSTRUCTIONS
    if context_data:
        try:
            ctx = json.loads(context_data)
            context_brief = f"\n\nENVIRONMENTAL CONTEXT:\n- Location: {ctx.get('district', 'Unknown')}\n- Soil: {ctx.get('soil_type', 'Unknown')}\n- Coordinates: {ctx.get('latitude')}, {ctx.get('longitude')}"
            localized_prompt += context_brief
        except: pass
    
    try:
        image_bytes_list = []
        pil_images = []
        for img_file in image_files:
            b = img_file.read()
            image_bytes_list.append(b)
            try:
                pil_images.append(Image.open(io.BytesIO(b)).convert("RGB"))
            except: continue

        if not pil_images: return jsonify({"error": "Invalid images"}), 400

        content = ""
        tier = "Alpha"

        if HAS_ALPHA:
            try:
                content = predict_primary(pil_images, localized_prompt)
            except Exception as e:
                logger.warning(f"Alpha analysis failed: {e}")

        if not content and HAS_BETA:
            tier = "Beta"
            try:
                content = predict_secondary(image_bytes_list, localized_prompt)
            except Exception as e:
                logger.warning(f"Beta analysis failed: {e}")

        if not content and HAS_CORE:
            logger.info("Falling back to local classifier.")
            return Response(predict_local(pil_images, lang), mimetype='application/json')

        if not content:
            return jsonify({"disease": "Service Unavailable", "confidence": 0, "treatment": "All engine tiers are offline."})

        if "```json" in content:
            content = content.split("```json")[1].split("```")[0].strip()
        elif "```" in content:
            content = content.split("```")[1].split("```")[0].strip()
            
        prediction = json.loads(content)
        prediction['disease'] = clean_output(prediction['disease'])
        prediction['treatment'] = clean_output(prediction['treatment'])
        prediction['engine_tier'] = tier
        
        logger.info(f"Diagnosis completed in {time.time() - start_time:.2f}s using {tier} tier.")
        return jsonify(prediction)

    except Exception as e:
        logger.error(f"Predict Endpoint Error: {e}")
        return jsonify({"disease": "Analysis Error", "confidence": 0, "treatment": str(e)}), 500

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5056, debug=False)