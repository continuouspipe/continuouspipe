#!/bin/sh

set -xe

exec rabbitmq-cli-consumer -e "/app/app/console -e=prod --with-headers simple-bus:consume" -i -c /usr/local/share/worker/configuration.conf
