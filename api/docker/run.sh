#!/bin/sh
set -xe

# Update parameters based on environment variables
composer run-script update-parameters

# Start Apache with the right permissions
/app/docker/start_safe_perms -DFOREGROUND

