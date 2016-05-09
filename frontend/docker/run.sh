#!/bin/sh
set -xe

# Updates the configuration file
#cd /app && \
#   grunt ngconstant && \
#   cp .tmp/scripts/config.js dist/scripts/config.js

# Start nginx
nginx -g "daemon off;"

