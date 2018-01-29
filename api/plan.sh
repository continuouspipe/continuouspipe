#!/bin/sh

alias_function do_start do_project_start_inner
function do_start() {
   composer run-script update-parameters

   do_project_start_inner
}

alias_function do_composer do_project_composer_inner
function do_composer() {
    if [ "$SKIP_COMPOSER" != "true" ]; then
        do_project_composer_inner
    fi
}
