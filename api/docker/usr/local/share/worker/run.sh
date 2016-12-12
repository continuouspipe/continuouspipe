#!/bin/sh

set -xe

# Update parameters based on environment
composer run-script update-parameters

# Configure Tideways' API
if [ -n "$TIDEWAYS_API_KEY" ]; then
    echo "tideways.api_key = $TIDEWAYS_API_KEY" >> /etc/php/7.0/cli/conf.d/40-tideways.ini
    echo "tideways.connection = tcp://tideways:9135" >> /etc/php/7.0/cli/conf.d/40-tideways.ini
fi

# Run consumer
rabbitmq-cli-consumer -e "/app/app/console -e=prod --with-headers simple-bus:consume" -i -c /usr/local/share/worker/configuration.conf
