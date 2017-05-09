#!/bin/sh

# Allow the user to override this script from their environment.
: ${AUTHENTICATOR_API_URL_OVERRIDE:="https://authenticator.continuouspipe.io"}
: ${RIVER_API_URL_OVERRIDE:="https://river.continuouspipe.io"}

AUTHENTICATOR_API_URL="$AUTHENTICATOR_API_URL_OVERRIDE" RIVER_API_URL="$RIVER_API_URL_OVERRIDE" \
    grunt serve --port=80
