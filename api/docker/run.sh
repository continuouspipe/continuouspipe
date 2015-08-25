#!/bin/sh
set -xe

# Update parameters based on environment variables
composer run-script update-parameters
app/console cache:clear -e=prod

# Start Apache
/usr/local/bin/apache2-foreground
