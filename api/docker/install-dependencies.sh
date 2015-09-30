#!/bin/sh

if [ -z "$GITHUB_TOKEN" ]; then
    echo "WARNING! GitHub token not found"
else
	composer config -g github-oauth.github.com $GITHUB_TOKEN
fi

composer install -o --no-interaction
