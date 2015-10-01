#!/bin/sh

set -xe

# Install dependencies
composer install

# Startup the application
docker-compose up -d

# Run migrations
docker-compose run api app/console doctrine:migrations:migrate --no-interaction

# Setup the queue
docker-compose run worker app/console rabbitmq:setup-fabric
