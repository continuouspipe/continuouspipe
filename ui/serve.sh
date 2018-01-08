#!/bin/sh

# Allow the user to override this script from their environment.
: ${RIVER_API_URL_OVERRIDE:="https://river.continuouspipe.io"}
: ${UI_PORT_OVERRIDE:="80"}

RIVER_API_URL="$RIVER_API_URL_OVERRIDE" \
    grunt serve --port="$UI_PORT_OVERRIDE"
