#!/bin/bash

function wait4tcp () {
    local nc opt silence op host port ret failed limit tries
    local OPTIND OPTARG

    limit=100
    op="open"

    while getopts ":csw:" opt; do
        case $opt in
            c)
                op="close"
                ;;
            s)
                silence=1
                ;;
            w)
                limit=$OPTARG
                ;;
            :)
                echo "$0: option requires an argument -- '$OPTARG'" 1>&2
                return 1
                ;;
            *)
                echo "$0: invalid option -- '$OPTARG'" 1>&2
                return 1
                ;;
        esac
    done
    shift $((OPTIND-1))

    if [[ $# -lt 2 ]]; then
        cat <<EOF
usage: tcp_open [OPTION]... HOST PORT...

  -c        wait until the port is closed. Otherwise wait until for opening.

  -s        silent mode
  -w COUNT  If number of retrying operation exceeds COUNT, return 1.
            (by default, COUNT is 100.  Use -1 for unlimited waiting)
EOF
        return 1;
    fi

    host=$1
    shift

    for port in "$@"; do
        test -z "$silence" && echo -n "Wait for TCP $host:$port $op..."

        tries=0
        while true; do
            if [[ $limit -gt 0 && $tries -ge $limit ]]; then
                failed=1
                break
            fi

            sleep 10

            ret=$?
            tries=$((tries + 1))
            if [ $op = "close" -a "$ret" -ne 0 ]; then
                break
            fi
            if [ $op = "open" -a "$ret" -eq 0 ]; then
                break
            fi
            echo "tries: $tries"
        done

        if [[ -z "$silence" ]]; then
            if [[ -z "$failed" ]]; then
                echo "done"
            else
                echo "failed"
            fi
        fi

        [[ -n "$failed" ]] && return 1;
    done

    return 0;
}

trap "exit 1" INT TERM QUIT

wait4tcp "$@"
