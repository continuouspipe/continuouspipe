#!/bin/sh

set -xe

EXTRA_ARGUMENTS=''
if [ ! -z "$WORKER_CONNECTION_NAME" ]; then
    EXTRA_ARGUMENTS=' --connection='$WORKER_CONNECTION_NAME
fi

exec /app/app/console -e=prod continuouspipe:message:pull-and-consume $EXTRA_ARGUMENTS
