#!/bin/bash

set -xe

EXTRA_ARGUMENTS=''
if [ ! -z "$WORKER_CONNECTION_NAME" ]; then
    EXTRA_ARGUMENTS=" --connection=${WORKER_CONNECTION_NAME}"
fi

if [ -z "$SYMFONY_ENV" ]; then
    SYMFONY_ENV=prod
fi

exec /app/bin/console -e="$SYMFONY_ENV" continuouspipe:message:pull-and-consume "${EXTRA_ARGUMENTS[@]}"
