#!/bin/sh

if [ -z "$MY_PRIVATE_ENVIRON" ]; then
    echo "Private variable is NOT FOUND"
else
    echo "Private variable is FOUND"
fi
