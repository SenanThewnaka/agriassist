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
import torch
from transformers import pipeline

# Load environment variables from parent directory
env_path = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '.env'))
print(f"Loading environment from: {env_path}")
load_dotenv(env_path, override=True)

app = Flask(__name__)

# --- ANALYSIS ENGINE CONFIGURATION ---
ALPHA_KEY = os.getenv("ENGINE_PROVIDER_ALPHA_KEY")
BETA_KEY = os.getenv("ENGINE_PROVIDER_BETA_KEY")

def mask_key(key: str) -> str:
    """Mask sensitive keys for logging."""
    if not key or len(key) < 8:
        return str(key)
    return key[:4] + "..." + key[-4:]

# 1. Setup Alpha Engine
HAS_ALPHA = False
if ALPHA_KEY and not ALPHA_KEY.startswith("YOUR"):
    print(f"Configuring Primary Engine with key: {mask_key(ALPHA_KEY)}")
    try:
        genai.configure(api_key=ALPHA_KEY)
        HAS_ALPHA = True
    except Exception as e:
        print(f"Primary Engine Config Error: {e}")

# 2. Setup Beta Engine
HAS_BETA = False
# Using the latest verified vision-capable model ID for fallbacks
BETA_MODEL = "meta-llama/llama-4-scout-17b-16e-instruct" 
if BETA_KEY and not BETA_KEY.startswith("YOUR"):
    print(f"Configuring Secondary Engine with key: {mask_key(BETA_KEY)}")
    try:
        beta_client = Groq(api_key=BETA_KEY)
        HAS_BETA = True
    except Exception as e:
        print(f"Secondary Engine Config Error: {e}")

# 3. Setup Core Analysis Engine
print("Initializing Core Analysis Engine...")
try:
    local_classifier = pipeline("image-classification", model="wambugu71/crop_leaf_diseases_vit")
    HAS_CORE = True
    print("Core Analysis Engine: [ACTIVE]")
except Exception as e:
    print(f"Core Engine Init Error: {e}")
    HAS_CORE = False

print(f"FINAL Engine Status: Alpha={'[ACTIVE]' if HAS_ALPHA else '[INACTIVE]'}, Beta={'[ACTIVE]' if HAS_BETA else '[INACTIVE]'}, Core={'[ACTIVE]' if HAS_CORE else '[INACTIVE]'}")

CORE_ANALYSIS_INSTRUCTIONS = """
System Role: Plant Pathology Diagnostic Service

Task: Analyze the provided plant image(s) to identify diseases, pests, or confirm health. Return ONLY a JSON object.

Output Format:
{
  "disease": "Exact name of disease/pest, or 'Healthy'",
  "confidence": <float, 0.0-1.0>,
  "treatment": "Actionable treatment protocol"
}

Constraints:
1. Multiple images provided in a single request represent the same sample. Consolidate analysis accordingly.
2. Support all global agricultural crops and plant life.
3. If the image is not related to plants or agriculture, return {"error": "Invalid Image"}.
"""

PLAN_GENERATION_INSTRUCTIONS = """
System Role: Agricultural Planning Service

Task: Generate a cultivation roadmap for the specified crop. Return ONLY a JSON object.

Format Requirements:
1. Output exactly 5-6 cultivation stages (e.g., Land Preparation, Sowing, Vegetative, Flowering, Harvesting).
2. Root object must include 'growth_days' (integer, total crop duration).
3. Each stage object must contain:
   - "name" (string): Stage name.
   - "days_from_start" (integer): Offset day from planting (0-indexed).
   - "advice" (string): Actionable agronomic recommendation.
   - "description" (string): Key targets/checklist.

Constraints:
- Output must be valid JSON.
- If a target language is specified, translate all text fields accordingly (except standardized chemical names).
"""

def generate_plan_primary(crop_name: str, variety_name: str = None, lang: str = 'en') -> str:
    # Using current 2026 stable-lite identifier
    model = genai.GenerativeModel('models/gemini-3.1-flash-lite-preview')
    
    prompt = PLAN_GENERATION_INSTRUCTIONS + f"\n\nRequest: Generate a plan for '{crop_name}'."
    if variety_name:
        prompt += f"\nSpecific Variety: '{variety_name}'"
        
    if lang == 'si':
        prompt = f"CRITICAL: YOU MUST RESPOND IN SINHALA LANGUAGE (සිංහල). EVERY text field (name, advice, description) MUST BE IN SINHALA. DO NOT USE ENGLISH FOR ANY TEXT. Technical chemical names stay in English.\n\n" + prompt
    elif lang == 'ta':
        prompt = f"CRITICAL: YOU MUST RESPOND IN TAMIL LANGUAGE (தமிழ்). EVERY text field (name, advice, description) MUST BE IN TAMIL. DO NOT USE ENGLISH FOR ANY TEXT. Technical chemical names stay in English.\n\n" + prompt
    
    response = model.generate_content(prompt, request_options={"timeout": 60})
    return response.text

def generate_plan_secondary(crop_name: str, variety_name: str = None, lang: str = 'en') -> str:
    prompt = PLAN_GENERATION_INSTRUCTIONS + f"\n\nRequest: Generate a plan for '{crop_name}'."
    if variety_name:
        prompt += f"\nVariety: '{variety_name}'"
        
    if lang == 'si':
        prompt = f"CRITICAL: YOU MUST RESPOND IN SINHALA LANGUAGE (සිංහල). EVERY text field (name, advice, description) MUST BE IN SINHALA. DO NOT USE ENGLISH FOR ANY TEXT. Technical chemical names stay in English.\n\n" + prompt
    elif lang == 'ta':
        prompt = f"CRITICAL: YOU MUST RESPOND IN TAMIL LANGUAGE (தமிழ்). EVERY text field (name, advice, description) MUST BE IN TAMIL. DO NOT USE ENGLISH FOR ANY TEXT. Technical chemical names stay in English.\n\n" + prompt

    completion = beta_client.chat.completions.create(
        model=BETA_MODEL,
        messages=[{"role": "user", "content": prompt}],
        temperature=0.1,
        max_tokens=2048,
        response_format={"type": "json_object"},
        timeout=60
    )
    return completion.choices[0].message.content

def predict_primary(imgs: list, prompt: str = CORE_ANALYSIS_INSTRUCTIONS) -> str:
    # Using current 2026 stable-lite identifier
    model = genai.GenerativeModel('models/gemini-3.1-flash-lite-preview')
    contents = [prompt] + imgs
    response = model.generate_content(contents, request_options={"timeout": 60})
    return response.text

def predict_secondary(image_bytes_list: list, prompt: str = CORE_ANALYSIS_INSTRUCTIONS) -> str:
    base64_image = base64.b64encode(image_bytes_list[0]).decode('utf-8')
    completion = beta_client.chat.completions.create(
        model=BETA_MODEL,
        messages=[
            {
                "role": "user",
                "content": [
                    {"type": "text", "text": prompt + f"\nNote: {len(image_bytes_list)} images were uploaded. I am showing you the primary specimen."},
                    {
                        "type": "image_url",
                        "image_url": {
                            "url": f"data:image/jpeg;base64,{base64_image}",
                        },
                    },
                ],
            }
        ],
        temperature=0.1,
        max_tokens=1024,
        response_format={"type": "json_object"},
        timeout=60
    )
    return completion.choices[0].message.content

def predict_local(pil_imgs, lang='en'):
    # Process the first image for local classifier
    results = local_classifier(pil_imgs[0])
    top_result = results[0]
    
    # Clean up the label which might be in format 'Plant__Disease'
    label = top_result['label'].replace('___', ': ').replace('__', ' ').replace('_', ' ').title()
    conf = float(top_result['score'])
    
    # Localized Fallback Strings
    placeholders = {
        'en': {
            'no_plant': "Expert analysis could not identify a valid plant in this image. Please ensure the leaf is centered, well-lit, and clearly visible.",
            'treatment': f"Core engine analysis detected {label}. Note: This is an offline diagnosis based on visual patterns. For a full professional protocol including chemical dosage, please ensure a stable internet connection for Cloud Engine verification."
        },
        'si': {
            'no_plant': "විශේෂඥ විශ්ලේෂණයට මෙම රූපයේ වලංගු ශාකයක් හඳුනාගත නොහැකි විය. කරුණාකර පත්‍රය මධ්‍යගතව, හොඳින් ආලෝකමත්ව සහ පැහැදිලිව පෙනෙන බව සහතික කර ගන්න.",
            'treatment': f"මූලික එන්ජින් විශ්ලේෂණය මගින් {label} හඳුනා ගන්නා ලදී. සටහන: මෙය දෘශ්‍ය රටා මත පදනම් වූ නොබැඳි රෝග විනිශ්චයකි. රසායනික මාත්‍රාව ඇතුළු පූර්ණ වෘත්තීය ප්‍රොටෝකෝලයක් සඳහා, කරුණාකර Cloud Engine සත්‍යාපනය සඳහා ස්ථාවර අන්තර්ජාල සම්බන්ධතාවයක් සහතික කරන්න."
        },
        'ta': {
            'no_plant': "நிபுணர் பகுப்பாய்வால் இந்த படத்தில் சரியான தாவரத்தை அடையாளம் காண முடியவில்லை. இலை மையமாக, நன்கு ஒளிரும் மற்றும் தெளிவாக இருப்பதை உறுதி செய்யவும்.",
            'treatment': f"கோர் என்ஜின் பகுப்பாய்வு {label}-ஐக் கண்டறிந்துள்ளது. குறிப்பு: இது காட்சி வடிவங்களின் அடிப்படையில் ஆஃப்லைன் நோயறிதல் ஆகும். இரசாயன அளவு உள்ளிட்ட முழுமையான தொழில்முறை நெறிமுறைக்கு, கிளவுட் என்ஜின் சரிபார்ப்பிற்கு நிலையான இணைய இணைப்பை உறுதிப்படுத்தவும்."
        }
    }

    # Use 'en' as default if lang not supported
    lang_key = lang if lang in placeholders else 'en'
    
    if label.lower() == "invalid":
        disease = "Unknown/No Plant"
        treatment = placeholders[lang_key]['no_plant']
    else:
        disease = f"{label} (Local)"
        treatment = placeholders[lang_key]['treatment']

    return json.dumps({
        "disease": disease,
        "treatment": treatment,
        "confidence": f"{int(conf*100)}% (Fallback)"
    })

@app.route('/generate-plan', methods=['POST'])
def generate_plan():
    """Generate a cultivation roadmap using AI."""
    start_time = time.time()
    data = request.get_json()
    crop_name = data.get('crop_name')
    variety_name = data.get('variety_name')
    lang = data.get('lang', 'en')

    if not crop_name:
        return jsonify({"error": "crop_name is required"}), 400

    print(f"\n--- New Plan Request: {crop_name} ({variety_name if variety_name else 'N/A'}) ({lang}) ---")

    try:
        content = ""
        # 🔵 Tier 1: Alpha (Gemini)
        if HAS_ALPHA:
            try:
                print(f"Generating plan via Alpha Engine...")
                content = generate_plan_primary(crop_name, variety_name, lang)
            except Exception as e:
                print(f"Alpha Plan Error: {e}")

        # 🟢 Tier 2: Beta (Groq)
        if not content and HAS_BETA:
            try:
                print(f"Generating plan via Beta Engine...")
                content = generate_plan_secondary(crop_name, variety_name, lang)
            except Exception as e:
                print(f"Beta Plan Error: {e}")

        if not content:
            return jsonify({
                "error": True, 
                "message": "AI roadmap generation is currently unavailable. Please try again later."
            }), 503

        # Sanitize JSON
        content = content.strip()
        if "```json" in content:
            content = content.split("```json")[1].split("```")[0].strip()
        elif "```" in content:
            content = content.split("```")[1].split("```")[0].strip()

        plan = json.loads(content)
        print(f"Plan generated in {time.time() - start_time:.2f}s")
        return jsonify(plan)

    except Exception as e:
        print(f"Plan Generation Failed: {e}")
        return jsonify({"error": str(e)}), 500
        print(f"Plan Generation Failed: {str(e)}")
        return jsonify({"error": str(e)}), 500

@app.route('/health', methods=['GET'])
def health():
    return jsonify({
        "status": "online",
        "timestamp": time.time(),
        "engine_tiers": {
            "alpha": "ACTIVE" if HAS_ALPHA else "INACTIVE",
            "beta": "ACTIVE" if HAS_BETA else "INACTIVE",
            "core": "ACTIVE" if HAS_CORE else "INACTIVE"
        }
    })

@app.route('/translate', methods=['POST'])
def translate():
    """Translate a given text to a target language using AI."""
    start_time = time.time()
    data = request.get_json()
    text = data.get('text')
    target_lang = data.get('lang', 'en')

    if not text:
        return jsonify({"error": "text is required"}), 400

    print(f"Translating text to {target_lang}...")

    lang_map = {
        'si': 'Sinhala (සිංහල)',
        'ta': 'Tamil (தமிழ்)',
        'en': 'English'
    }
    
    target_name = lang_map.get(target_lang, 'English')
    
    prompt = f"Translate the following agricultural diagnostic text to {target_name}. Keep technical chemical names in English. Return ONLY the translated text:\n\n{text}"

    try:
        content = ""
        if HAS_ALPHA:
            model = genai.GenerativeModel('models/gemini-3.1-flash-lite-preview')
            response = model.generate_content(prompt)
            content = response.text.strip()
        elif HAS_BETA:
            completion = beta_client.chat.completions.create(
                model=BETA_MODEL,
                messages=[{"role": "user", "content": prompt}],
                temperature=0.1
            )
            content = completion.choices[0].message.content.strip()

        if not content:
            return jsonify({"translated": text}) # Fallback to original

        return jsonify({"translated": content})

    except Exception as e:
        print(f"Translation Failed: {e}")
        return jsonify({"translated": text, "error": str(e)})

@app.route('/predict', methods=['POST'])
def predict():
    start_time = time.time()
    print(f"\n--- New Request: {time.strftime('%H:%M:%S')} ---")
    
    # Handle both single 'image' and multiple 'images[]'
    image_files = request.files.getlist('images[]')
    lang = request.args.get('lang', 'en')
    
    if not image_files and 'image' in request.files:
        image_files = [request.files['image']]
    
    if not image_files:
        return jsonify({"error": "No images uploaded"}), 400
    
    print(f"Received {len(image_files)} samples for analysis. Requested language: {lang}")
    
    # Configure localized instructions
    localized_prompt = CORE_ANALYSIS_INSTRUCTIONS
    if lang == 'si':
        localized_prompt += "\n📝 CRITICAL TRANSLATION INSTRUCTION: You MUST respond in Sinhala language for the 'disease' and 'treatment' fields. HOWEVER, any chemical names (e.g., fungicides, fertilizers) or highly specific scientific taxonomy MUST remain in English."
    elif lang == 'ta':
        localized_prompt += "\n📝 CRITICAL TRANSLATION INSTRUCTION: You MUST respond in Tamil language for the 'disease' and 'treatment' fields. HOWEVER, any chemical names (e.g., fungicides, fertilizers) or highly specific scientific taxonomy MUST remain in English."
    
    image_bytes_list = []
    pil_images = []
    
    for img_file in image_files:
        b = img_file.read()
        image_bytes_list.append(b)
        try:
            pil_images.append(Image.open(io.BytesIO(b)).convert("RGB"))
        except Exception as e:
            print(f"Skipping invalid image: {e}")

    if not pil_images:
        return jsonify({"error": "No valid images provided"}), 400

    try:
        content = ""
        # 🔵 Tier 1: Alpha (Best for Multi-Image/Context)
        if HAS_ALPHA:
            try:
                print(f"Attempting multi-sample analysis (Tier: Alpha, Lang: {lang})...")
                content = predict_primary(pil_images, localized_prompt)
            except Exception as me:
                print(f"Alpha engine failed: {me}")

        # 🟢 Tier 2: Beta (High-Speed Fallback)
        if not content and HAS_BETA:
            try:
                print(f"Attempting fallback analysis (Tier: Beta)...")
                content = predict_secondary(image_bytes_list, localized_prompt)
            except Exception as ge:
                print(f"Beta engine failed: {ge}")

        # 🔴 Tier 3: Core Engine
        if not content and HAS_CORE:
            print("Falling back to Core Engine...")
            content = predict_local(pil_images, lang)

        if not content:
            return jsonify({
                "disease": "System Overload",
                "confidence": 0.0,
                "treatment": "Analysis nodes are currently offline. Please check your network connection or verify provider keys."
            })

        # Sanitize JSON from Engine outputs
        content = content.strip()
        if "```json" in content:
            content = content.split("```json")[1].split("```")[0].strip()
        elif "```" in content:
            content = content.split("```")[1].split("```")[0].strip()
            
        prediction = json.loads(content)
        print(f"Analysis complete in {time.time() - start_time:.2f}s")
        return jsonify(prediction)

    except Exception as e:
        print(f"Engine Failure: {str(e)}")
        return jsonify({
            "disease": "Analysis Interrupted",
            "confidence": 0.0,
            "treatment": f"An error occurred: {str(e)}. Please try a different photo."
        }), 500

if __name__ == '__main__':
    print("AgriAssist Multi-Sample Analysis Engine starting on 0.0.0.0:5055...")
    app.run(host='0.0.0.0', port=5055, debug=False)
