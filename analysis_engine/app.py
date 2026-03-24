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
You are a Professional Agricultural Plant Pathologist specializing in Sri Lankan crops.
Analyze the provided image(s) and return ONLY a JSON response:
{
  "disease": "Consolidated Disease Name or Healthy",
  "confidence": 0.95,
  "treatment": "Detailed step-by-step treatment protocol."
}
If multiple images are provided, they are from the SAME plant. Use all of them to make a more accurate consolidated diagnosis.
Focus on Paddy, Tea, Banana, Coconut. If not a plant, return "Invalid Image".
"""

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

def predict_local(pil_imgs):
    # Process the first image for local classifier
    results = local_classifier(pil_imgs[0])
    top_result = results[0]
    
    # Clean up the label which might be in format 'Plant__Disease'
    label = top_result['label'].replace('___', ': ').replace('__', ' ').replace('_', ' ').title()
    conf = float(top_result['score'])
    
    # User-friendly handling for 'Invalid' classification
    display_label = label
    if label.lower() == "invalid":
        display_label = "Non-Plant or Unclear Specimen"
        treatment = "Expert analysis could not identify a valid plant in this image. Please ensure the leaf is centered, well-lit, and clearly visible."
    else:
        treatment = f"Core engine analysis detected {label}. Note: This is an offline diagnosis based on visual patterns. For a full professional protocol including chemical dosage, please ensure a stable internet connection for Cloud Engine verification."

    return json.dumps({
        "disease": display_label,
        "confidence": conf,
        "treatment": treatment
    })

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
            content = predict_local(pil_images)

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
