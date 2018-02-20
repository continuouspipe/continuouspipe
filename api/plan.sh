#!/bin/bash

alias_function do_start do_project_start_inner
function do_start() {
    # Update the parameters. This is still required by things like the `SENTRY_DSN` configuration
    # that cannot use `%env(...)%`
    composer run-script update-parameters

    # If there is the Docker unix socket, grant access to it
    if [ "$GRANT_DOCKER_DAEMON" = "true" ]; then
        chmod 777 /var/run/docker.sock || echo "Warning: The Docker daemon doesn't seems to exists"
    fi

    do_project_start_inner
}

alias_function do_composer do_project_composer_inner
function do_composer() {
    if [ "$SKIP_COMPOSER" != "true" ]; then
        do_project_composer_inner
    fi
}
