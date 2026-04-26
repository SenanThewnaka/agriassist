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
from typing import List, Optional, Union, Dict, Any

"""
AgriAssist Intelligence Engine
------------------------------
Cascading AI backend for agricultural image analysis, localized cultivation 
planning, and biological threat prediction.

Architecture:
- Tier 1 (Alpha): Gemini 1.5 Pro (Multimodal & Planning)
- Tier 2 (Beta): Llama 3 / Groq (High-speed structured extraction)
- Tier 3 (Core): Local ViT Classifier (Offline/Resilience fallback)
"""

# Configure Logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

app = Flask(__name__)

# --- Engine Configuration & Initialization ---

# Tier 1: Gemini (Primary Multimodal)
ALPHA_KEY = os.environ.get('ENGINE_PROVIDER_ALPHA_KEY')
ALPHA_MODEL = "gemini-1.5-pro"
HAS_ALPHA = bool(ALPHA_KEY)
if HAS_ALPHA:
    genai.configure(api_key=ALPHA_KEY)

# Tier 2: Groq (Primary Text/JSON)
BETA_KEY = os.environ.get('ENGINE_PROVIDER_BETA_KEY')
BETA_MODEL = "llama-3.3-70b-versatile"
HAS_BETA = bool(BETA_KEY)
if HAS_BETA:
    beta_client = Groq(api_key=BETA_KEY)

# Tier 3: Local Resilience (Offline classification)
try:
    from transformers import pipeline
    local_classifier = pipeline("image-classification", model="wambugu71/crop_leaf_diseases_vit")
    HAS_CORE = True
    logger.info("Core Engine (Local) initialized for offline resilience.")
except Exception as e:
    HAS_CORE = False
    logger.error(f"Core Engine Initialization Failure: {e}")

# --- Constants & System Prompts ---

CORE_ANALYSIS_INSTRUCTIONS = """
You are a Professional Plant Pathologist. Analyze the provided crop leaf images.
Determine the disease, severity (Low/Medium/High), and organic/chemical treatment.
Response MUST be valid JSON:
{
  "disease": "Disease Name",
  "confidence": 0.95,
  "severity": "High",
  "spread_risk": "High",
  "treatment": "Detailed instructions"
}
"""

# --- Internal Utility Methods ---

def clean_output(text: str) -> str:
    """Removes common AI artifacts and markdown formatting from raw strings."""
    return re.sub(r'[*_`]', '', text).strip()

def predict_primary(images: List[Image.Image], prompt: str) -> str:
    """Executes Multimodal Analysis using the Alpha (Gemini) tier."""
    model = genai.GenerativeModel(ALPHA_MODEL)
    response = model.generate_content([prompt] + images)
    return response.text

def predict_secondary(image_bytes_list: List[bytes], prompt: str) -> str:
    """Executes Analysis using the Beta (Groq/Llama) tier via Base64 encoding."""
    # Note: Llama-3 Vision handles single images optimally.
    base64_image = base64.b64encode(image_bytes_list[0]).decode('utf-8')
    completion = beta_client.chat.completions.create(
        model=BETA_MODEL,
        messages=[{
            "role": "user",
            "content": [
                {"type": "text", "text": prompt},
                {"type": "image_url", "image_url": {"url": f"data:image/jpeg;base64,{base64_image}"}}
            ]
        }],
        temperature=0.1,
        response_format={"type": "json_object"}
    )
    return completion.choices[0].message.content

# --- API Endpoints ---

@app.route('/health', methods=['GET'])
def health_check() -> Response:
    """System health and engine tier status manifest."""
    return jsonify({
        "status": "online",
        "engine_tiers": {
            "alpha": "ACTIVE" if HAS_ALPHA else "INACTIVE",
            "beta": "ACTIVE" if HAS_BETA else "INACTIVE",
            "core": "ACTIVE" if HAS_CORE else "INACTIVE"
        }
    })

@app.route('/generate-plan', methods=['POST'])
def generate_plan() -> Response:
    """Generates a granular cultivation roadmap using cascading intelligence."""
    data = request.get_json()
    prompt = data.get('prompt')
    if not prompt: return jsonify({"error": "Missing prompt"}), 400

    try:
        content = ""
        # Cascade Logic: Prefer Beta (Groq) for structured JSON speed, fallback to Alpha.
        if HAS_BETA:
            try:
                groq_prompt = prompt if "json" in prompt.lower() else f"{prompt}\nReturn as json."
                completion = beta_client.chat.completions.create(
                    model=BETA_MODEL,
                    messages=[{"role": "user", "content": groq_prompt}],
                    temperature=0.1,
                    response_format={"type": "json_object"}
                )
                content = completion.choices[0].message.content.strip()
            except Exception as e:
                logger.warning(f"Beta Plan Generation Failure: {e}")

        if not content and HAS_ALPHA:
            try:
                model = genai.GenerativeModel(ALPHA_MODEL)
                response = model.generate_content(prompt)
                content = response.text.strip()
            except Exception as e:
                logger.error(f"Alpha Plan Generation Failure: {e}")

        if not content: return jsonify({"error": "Intelligence tiers offline"}), 503

        # Extraction: Handle potential markdown code blocks in raw text
        if "```json" in content:
            content = content.split("```json")[1].split("```")[0].strip()
        elif "```" in content:
            content = content.split("```")[1].split("```")[0].strip()

        return jsonify(json.loads(content))
    except Exception as e:
        logger.error(f"Generate Plan Exception: {e}")
        return jsonify({"error": str(e)}), 500

@app.route('/predict', methods=['POST'])
def predict() -> Response:
    """Main image analysis endpoint for biological diagnostic tasks."""
    image_files = request.files.getlist('images[]')
    if not image_files: return jsonify({"error": "No images provided"}), 400
    
    localized_prompt = CORE_ANALYSIS_INSTRUCTIONS
    context_data = request.form.get('context')
    if context_data:
        try:
            ctx = json.loads(context_data)
            localized_prompt += f"\n\nENVIRONMENTAL CONTEXT: District: {ctx.get('district')}, Soil: {ctx.get('soil_type')}"
        except: pass
    
    try:
        image_bytes_list = [img.read() for img in image_files]
        pil_images = [Image.open(io.BytesIO(b)).convert("RGB") for b in image_bytes_list]
        
        content = ""
        tier = "Alpha"

        # Multimodal Cascade
        if HAS_ALPHA:
            try: content = predict_primary(pil_images, localized_prompt)
            except Exception as e: logger.warning(f"Alpha Diagnostic Failure: {e}")

        if not content and HAS_BETA:
            tier = "Beta"
            try: content = predict_secondary(image_bytes_list, localized_prompt)
            except Exception as e: logger.warning(f"Beta Diagnostic Failure: {e}")

        if not content and HAS_CORE:
            logger.info("Engaging local resilience tier.")
            # Local fallback uses standard Vit model output format
            preds = local_classifier(pil_images[0])
            return jsonify({
                "disease": preds[0]['label'],
                "confidence": preds[0]['score'],
                "engine_tier": "Core",
                "treatment": "Consult local extension officer for offline diagnostic confirmation."
            })

        if not content: return jsonify({"disease": "Tiers Offline", "confidence": 0}), 503

        # Clean and Parse
        if "```json" in content: content = content.split("```json")[1].split("```")[0].strip()
        prediction = json.loads(content)
        prediction['engine_tier'] = tier
        
        return jsonify(prediction)
    except Exception as e:
        logger.error(f"Predict Exception: {e}")
        return jsonify({"error": str(e)}), 500

@app.route('/analyze-soil', methods=['POST'])
def analyze_soil() -> Response:
    """Parses chemical telemetry from soil test documents via OCR/Vision."""
    image_files = request.files.getlist('images[]')
    if not image_files: return jsonify({"error": "Missing documents"}), 400

    prompt = "You are a Soil Scientist. Extract metrics: soil_type, ph_level, nitrogen, phosphorus, potassium, organic_matter, and recommendation. Return JSON only."

    try:
        pil_images = [Image.open(io.BytesIO(img.read())).convert("RGB") for img in image_files]
        content = predict_primary(pil_images, prompt) if HAS_ALPHA else ""
        
        if "```json" in content: content = content.split("```json")[1].split("```")[0].strip()
        return jsonify(json.loads(content))
    except Exception as e:
        return jsonify({"error": str(e)}), 500

@app.route('/suggest-varieties', methods=['POST'])
def suggest_varieties() -> Response:
    """Suggests suitable varieties for a custom crop based on soil type."""
    data = request.get_json()
    crop_name = data.get('crop_name')
    soil_type = data.get('soil_type')

    if not crop_name:
        return jsonify({"error": "crop_name is required"}), 400

    prompt = f"Act as a Sri Lankan Agriculture Expert. Suggest the top 3 most suitable commercial seed varieties for '{crop_name}' that thrive in '{soil_type}' soil in Sri Lanka. Return ONLY a JSON array of strings: [\"Variety 1\", \"Variety 2\", \"Variety 3\"]. No explanation, no keys, just the array."

    try:
        content = ""
        if HAS_BETA:
            completion = beta_client.chat.completions.create(
                model=BETA_MODEL,
                messages=[{"role": "user", "content": prompt}],
                temperature=0.1,
            )
            content = completion.choices[0].message.content
        elif HAS_ALPHA:
            model = genai.GenerativeModel(ALPHA_MODEL)
            response = model.generate_content(prompt)
            content = response.text

        logger.info(f"Raw variety content: {content}")

        # Clean JSON markdown if present
        if "```json" in content:
            content = content.split("```json")[1].split("```")[0].strip()
        elif "```" in content:
            content = content.split("```")[1].split("```")[0].strip()
        
        # Remove any leading/trailing non-JSON text
        content_match = re.search(r'\[.*\]', content.replace('\n', ''))
        if not content_match:
            return jsonify([])
        
        parsed = json.loads(content_match.group())
        logger.info(f"Parsed raw object: {parsed}")

        # Find the array of varieties no matter where the AI hid it
        variety_list = []
        if isinstance(parsed, list):
            variety_list = parsed
        elif isinstance(parsed, dict):
            # Check common keys
            for key in ['varieties', 'suitable_varieties', 'suggestions', 'seeds']:
                if key in parsed and isinstance(parsed[key], list):
                    variety_list = parsed[key]
                    break
                # Check one level deeper (e.g., varieties.suitable_varieties)
                if key in parsed and isinstance(parsed[key], dict):
                    for sub_key in ['suitable_varieties', 'varieties', 'list']:
                        if sub_key in parsed[key] and isinstance(parsed[key][sub_key], list):
                            variety_list = parsed[key][sub_key]
                            break
            
            # Final fallback: just find the first list in the dictionary
            if not variety_list:
                for val in parsed.values():
                    if isinstance(val, list):
                        variety_list = val
                        break
        
        # Format for frontend: ensure it's always an array of objects {name: "..."}
        formatted = []
        for item in variety_list:
            name = item.get('name') or item.get('variety_name') if isinstance(item, dict) else str(item)
            if name:
                formatted.append({"name": name})
        
        return jsonify(formatted)
    except Exception as e:
        logger.error(f"Variety Suggestion Error: {e}")
        return jsonify([])

@app.route('/translate', methods=['POST'])
def translate_text() -> Response:
    """Translates agricultural text into Sinhala or Tamil using AI."""
    data = request.get_json()
    text = data.get('text')
    lang = data.get('lang') # 'si' or 'ta'

    if not text or not lang:
        return jsonify({"error": "text and lang are required"}), 400

    target = "Sinhala" if lang == 'si' else "Tamil"
    prompt = f"Translate the following agricultural text into natural, professional {target}. Return ONLY the translated text, no extra commentary.\n\nText: {text}"

    try:
        content = ""
        if HAS_BETA:
            completion = beta_client.chat.completions.create(
                model=BETA_MODEL,
                messages=[{"role": "user", "content": prompt}],
                temperature=0.1
            )
            content = completion.choices[0].message.content.strip()
        elif HAS_ALPHA:
            model = genai.GenerativeModel(ALPHA_MODEL)
            response = model.generate_content(prompt)
            content = response.text.strip()

        return jsonify({"translated": content})
    except Exception as e:
        logger.error(f"AI Translation Error: {e}")
        return jsonify({"error": str(e)}), 500

@app.route('/recommend-date', methods=['POST'])
def recommend_date() -> Response:
    """Recommends an optimal planting date based on 14-day weather telemetry."""
    data = request.get_json()
    crop = data.get('crop')
    weather = data.get('weather')
    soil = data.get('soil')

    if not crop or not weather:
        return jsonify({"error": "Missing context"}), 400

    prompt = f"""
    As an Agricultural Consultant, analyze this 14-day forecast for '{crop}' in '{soil}' soil.
    Forecast: {json.dumps(weather)}
    
    Identify the best date to start planting within these 14 days to minimize rain damage or heat stress.
    Return ONLY a JSON object: {{"recommended_date": "YYYY-MM-DD", "reason": "One sentence expert reason"}}
    """

    try:
        content = ""
        if HAS_BETA:
            completion = beta_client.chat.completions.create(
                model=BETA_MODEL,
                messages=[{"role": "user", "content": prompt}],
                temperature=0.1,
                response_format={"type": "json_object"}
            )
            content = completion.choices[0].message.content
        elif HAS_ALPHA:
            model = genai.GenerativeModel(ALPHA_MODEL)
            response = model.generate_content(prompt)
            content = response.text

        if "```json" in content: content = content.split("```json")[1].split("```")[0].strip()
        return jsonify(json.loads(content))
    except Exception as e:
        logger.error(f"Date Recommendation Error: {e}")
        return jsonify({"recommended_date": time.strftime("%Y-%m-%d"), "reason": "Error consulting AI, defaulted to today."})

@app.route('/predict-pests', methods=['POST'])
def predict_pests() -> Response:
    """Predicts biological risk vectors based on meteorological forecast data."""
    data = request.get_json()
    crop = data.get('crop')
    weather = data.get('weather')
    if not crop or not weather: return jsonify({"error": "Context incomplete"}), 400

    prompt = f"Expert Pest Control: Analyze 14-day weather for {crop}. Return JSON array of objects: pest_name, risk_level, message, recommended_action."

    try:
        content = ""
        if HAS_BETA:
            completion = beta_client.chat.completions.create(
                model=BETA_MODEL,
                messages=[{"role": "user", "content": prompt}],
                temperature=0.2,
                response_format={"type": "json_object"}
            )
            content = completion.choices[0].message.content
        
        if content:
            if "```json" in content: content = content.split("```json")[1].split("```")[0].strip()
            return jsonify(json.loads(content))
        return jsonify([])
    except Exception as e:
        logger.error(f"Pest Prediction Error: {e}")
        return jsonify([])

@app.route('/translate', methods=['POST'])
def translate() -> Response:
    """Translates text to target language using the available AI tiers."""
    data = request.get_json()
    text = data.get('text')
    lang = data.get('lang')
    if not text or not lang: return jsonify({"error": "Missing text/lang"}), 400

    prompt = f"Translate the following agricultural text to {lang}. Return ONLY the translated text. No explanation, no intro, just the translation.\n\nText: {text}"

    try:
        content = ""
        if HAS_BETA:
            completion = beta_client.chat.completions.create(
                model=BETA_MODEL,
                messages=[{"role": "user", "content": prompt}],
                temperature=0.1,
            )
            content = completion.choices[0].message.content.strip()
        elif HAS_ALPHA:
            model = genai.GenerativeModel(ALPHA_MODEL)
            response = model.generate_content(prompt)
            content = response.text.strip()
        
        return jsonify({"translated": content})
    except Exception as e:
        logger.error(f"Translation Error: {e}")
        return jsonify({"error": str(e)}), 500

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5056, debug=False)
