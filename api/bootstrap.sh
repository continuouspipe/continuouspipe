#!/bin/sh

set -xe

# Startup the application
docker-compose up -d

# Install dependencies
docker-compose run api composer install

# Run migrations
docker-compose run api app/console doctrine:migrations:migrate --no-interaction
