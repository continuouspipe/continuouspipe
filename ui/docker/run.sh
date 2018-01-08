#!/bin/sh
set -xe

# Updates the configuration file
echo "
angular.module('config', [])
.constant('RIVER_API_URL', '"$RIVER_API_URL"')
.constant('LOG_STREAM_API_URL', '"$LOG_STREAM_API_URL"')
.constant('PIPE_API_URL', '"$PIPE_API_URL"')
.constant('SENTRY_DSN', '"$SENTRY_DSN"')
.constant('KUBE_PROXY_HOSTNAME', '"$KUBE_PROXY_HOSTNAME"')
.constant('KUBE_STATUS_API_URL', '"$KUBE_STATUS_API_URL"')
;" > /app/dist/scripts/config.js

# Start nginx
nginx -g "daemon off;"
