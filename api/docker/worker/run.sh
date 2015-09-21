#!/bin/bash

set -xe

# Update parameters based on environment
composer run-script update-parameters

# Removes cache
rm -rf app/cache/* && rm -rf app/logs/*

/usr/bin/supervisord
