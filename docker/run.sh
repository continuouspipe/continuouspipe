#!/bin/bash
set -xe

echo "Waiting MongoDB to be accessible"
/app/docker/tcp-port-scan.sh mongodb 27017

echo "Mongo is accessible, wait 1 second before starting Meteor"
sleep 1

bash $METEORD_DIR/run_app.sh
