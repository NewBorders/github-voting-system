#!/bin/bash

set -e

echo "🚀 Starting Feature Voting Backend Setup..."

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Check if .env exists
if [ ! -f .env ]; then
    echo -e "${BLUE}Creating .env file from .env.example...${NC}"
    cp .env.example .env
    echo -e "${GREEN}✓ .env file created${NC}"
else
    echo -e "${GREEN}✓ .env file already exists${NC}"
fi

# Build and start containers
echo -e "${BLUE}Building and starting Docker containers...${NC}"
docker-compose up -d --build

# Wait for MySQL to be ready
echo -e "${BLUE}Waiting for MySQL to be ready...${NC}"
sleep 10

# Install composer dependencies
echo -e "${BLUE}Installing Composer dependencies...${NC}"
docker-compose exec -T app composer install --no-interaction --prefer-dist --optimize-autoloader

# Generate application key
echo -e "${BLUE}Generating application key...${NC}"
docker-compose exec -T app php artisan key:generate

# Run migrations
echo -e "${BLUE}Running database migrations...${NC}"
docker-compose exec -T app php artisan migrate --force

# Seed database
echo -e "${BLUE}Seeding database with sample data...${NC}"
docker-compose exec -T app php artisan db:seed

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}✓ Setup completed successfully!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "API is available at: ${BLUE}http://localhost:8080${NC}"
echo -e "Health check: ${BLUE}http://localhost:8080/up${NC}"
echo ""
echo -e "Next steps:"
echo -e "1. Update ADMIN_API_TOKEN in .env file"
echo -e "2. View logs: ${BLUE}docker-compose logs -f${NC}"
echo -e "3. Run tests: ${BLUE}docker-compose exec app php artisan test${NC}"
echo ""
