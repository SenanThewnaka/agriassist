FROM python:3.11-slim

WORKDIR /app

# Install system dependencies for Pillow and Torch
RUN apt-get update && apt-get install -y \
    libgl1 \
    libglib2.0-0 \
    && rm -rf /var/lib/apt/lists/*

COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY . .

# Expose the port the app runs on
EXPOSE 5055

# Run the application
CMD ["python", "app.py"]
