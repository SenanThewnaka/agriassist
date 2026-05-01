from groq import Groq
import os

BETA_KEY = os.environ.get('ENGINE_PROVIDER_BETA_KEY')
if BETA_KEY:
    client = Groq(api_key=BETA_KEY)
    try:
        models = client.models.list()
        print("Available Groq models:")
        for m in models.data:
            print(m.id)
    except Exception as e:
        print(f"Error: {e}")
else:
    print("No Groq API key found.")
