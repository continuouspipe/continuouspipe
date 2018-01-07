#!/bin/sh

set -ex

/app/bin/console tolerance:metrics:collect-and-publish
