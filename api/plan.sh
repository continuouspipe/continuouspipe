#!/bin/sh

alias_function do_start do_project_start_inner
function do_start() {
   composer run-script update-parameters

   do_project_start_inner
}
