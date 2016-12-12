#!/bin/sh

set -ex

/app/app/console tolerance:metrics:collect-and-publish
