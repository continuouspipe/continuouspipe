#!/bin/sh
set -xe

# Updates the configuration file
echo "
angular.module('config', [])
.constant('RIVER_API_URL', '${RIVER_API_URL}')
.constant('LOG_STREAM_API_URL', '${LOG_STREAM_API_URL}')
.constant('PIPE_API_URL', '${PIPE_API_URL}')
.constant('SENTRY_DSN', '${UI_SENTRY_DSN}')
.constant('KUBE_STATUS_API_URL', '${KUBE_STATUS_API_URL}')
.constant('INTERCOM_ENABLED', '${INTERCOM_ENABLED}')
.constant('INTERCOM_APPLICATION_ID', '${INTERCOM_APPLICATION_ID}')
.constant('STATIS_METER_ENABLED', '${STATIS_METER_ENABLED}')
.constant('STATIS_METER_WRITE_KEY', '${STATIS_METER_WRITE_KEY}')
.constant('BILLING_ENABLED', '${BILLING_ENABLED}')
.constant('GOOGLE_ANALYTICS_TRACKER', '${GOOGLE_ANALYTICS_TRACKER}')
.constant('FIREBASE_APP', '${FIREBASE_APP}')
.constant('FIREBASE_WEB_API_KEY', '${FIREBASE_WEB_API_KEY}')
.constant('MANAGED_CLUSTER_ENABLED', '${MANAGED_CLUSTER_ENABLED}')
.constant('MANAGED_REGISTRY_ENABLED', '${MANAGED_REGISTRY_ENABLED}')
;" > /app/dist/scripts/config.js

# Start nginx
nginx -g "daemon off;"
