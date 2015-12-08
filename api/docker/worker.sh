#!/bin/sh

set -xe

# Update parameters based on environment
composer run-script update-parameters

# Run consumer
rabbitmq-cli-consumer -e "/app/app/console -e=prod simple-bus:consume" -c /app/docker/worker/configuration.conf
