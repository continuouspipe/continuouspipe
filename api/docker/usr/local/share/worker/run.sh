#!/bin/sh

set -xe

exec rabbitmq-cli-consumer -e "/app/app/console -e=prod --with-headers worker:consume" -i --strict-exit-code -c /usr/local/share/worker/configuration.conf
