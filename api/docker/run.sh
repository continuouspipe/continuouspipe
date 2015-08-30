#!/bin/sh
set -xe

# Update parameters based on environment variables
composer run-script update-parameters

# Run database migrations
php app/console doctrine:migrations:migrate --no-interaction

# Fixes permissions
rm -rf app/cache/* && rm -rf app/logs/* && \
	chown -R www-data:www-data app/cache && chown -R www-data:www-data app/logs

# Start Apache
/usr/local/bin/apache2-foreground
