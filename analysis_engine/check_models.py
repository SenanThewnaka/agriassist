import google.generativeai as genai
import os

ALPHA_KEY = os.environ.get('ENGINE_PROVIDER_ALPHA_KEY')
if ALPHA_KEY:
    genai.configure(api_key=ALPHA_KEY)
    try:
        print("Available models:")
        for m in genai.list_models():
            if 'generateContent' in m.supported_generation_methods:
                print(m.name)
    except Exception as e:
        print(f"Error: {e}")
else:
    print("No API key found.")
