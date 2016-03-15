#!/bin/sh
set -xe

# Updates the configuration file
cd /build && \
   grunt ngconstant && \
   cp .tmp/scripts/config.js /usr/share/nginx/html/scripts/config.js

# Start nginx
nginx -g "daemon off;"
