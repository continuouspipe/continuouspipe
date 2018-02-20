#!/bin/bash

HELPERS_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

melody_run() {
    command -v melody >/dev/null 2>&1 || {
        echo "This script requires Melody (melody.sensiolabs.org) to be installed."

        read -p "Do you want us to install it for you? " -n 1 -r
        echo    # (optional) move to a new line
        if [[ "$REPLY" =~ ^[Yy]$ ]]; then
            sudo sh -c "curl http://get.sensiolabs.org/melody.phar -o /usr/local/bin/melody && chmod a+x /usr/local/bin/melody"
        else
            exit 1
        fi
    }

    melody run "$@"
}

run_companienv() {
    melody_run "$HELPERS_DIR/companienv.php"
}

wait_for() {
    php "$HELPERS_DIR/wait-for.php" "$@"
}
