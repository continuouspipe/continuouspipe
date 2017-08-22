#!/bin/sh

echo $1 | base64 --decode
echo "With the following attributes"
echo $2 | base64 --decode
