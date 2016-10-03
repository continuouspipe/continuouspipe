#!/bin/sh
set -xe

# Update parameters based on environment variables
composer run-script update-parameters

# Configure Tideways' API
if [ -n "$TIDEWAYS_API_KEY" ]; then
    echo "tideways.api_key = $TIDEWAYS_API_KEY" >> /etc/php5/apache2/php.ini
    echo "tideways.connection = tcp://tideways:9135" >> /etc/php5/apache2/php.ini
fi

# Start Apache with the right permissions
/app/docker/start_safe_perms -DFOREGROUND
