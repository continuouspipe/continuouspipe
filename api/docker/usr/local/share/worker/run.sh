#!/bin/sh

set -xe

exec /usr/local/bin/puller \
    -google-project-id=continuous-pipe-1042 \
    -service-account-file-path=/app/app/var/google/river-pub-sub-service-account.json \
    -script-path="/app/app/console -e=prod continuouspipe:message:consume" \
    -subscription=$GOOGLE_PUB_SUB_SUBSCRIPTION_NAME
