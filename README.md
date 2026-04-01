# AgriAssist

AgriAssist is a technical solution developed to help Sri Lankan farmers manage crop diseases and plan their cultivation cycles. I built this to provide a reliable way to get expert-level diagnostic feedback directly in the field, even when specialized agricultural officers are hard to reach.

The system uses a dedicated analysis engine to process leaf images and provide treatment protocols for all global agricultural crops. It also integrates localized weather data to provide a 7-day outlook for resource management.

## Core Features

- Analysis Engine: A multi-specimen scanner that analyzes plant samples for diseases and provides treatment steps.
- Weather Integration: 7-day precipitation and temperature modeling for specific agricultural zones.
- Seasonal Planner: A tool to calculate harvest dates and align cultivation with the Maha and Yala seasons.
- Language Support: The entire interface is available in English, Sinhala, and Tamil.
- Field Resilience: Designed to work under varied network conditions typical of rural farming areas.

## Getting Started

You will need Docker and Docker Compose installed on your machine to run the full stack.

1. Clone the repository:
   git clone https://github.com/SenanThewnaka/agriassist.git
   cd agriassist

2. Set up environment:
   cp .env.example .env
   # Add your analysis engine keys and URLs to the .env file.

3. Start the containers:
   docker-compose up -d

4. Initialize the application:
   docker exec -it agriassist-app php artisan migrate
   docker exec -it agriassist-app php artisan storage:link

## Technical Overview

This project is built using Laravel for the web interface and a Python-based Flask service for the analysis logic. The analysis engine uses specialized classification models to identify disease patterns. Data is stored in a MySQL database, and the entire system is containerized for consistent deployment.

## License

This project is released under a personal and educational license. You are welcome to explore the code to see how it works or use it for your own learning. However, commercial use or selling copies of this software is strictly prohibited. See the LICENSE file for full details.

Developed by Senan Thewnaka.
