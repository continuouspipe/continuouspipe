#!/bin/sh

set -xe

exec /app/app/console -e=prod continuouspipe:message:pull-and-consume
