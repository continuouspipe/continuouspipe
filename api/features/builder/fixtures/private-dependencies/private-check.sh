#!/bin/sh

sleep 5

if [ -z "$MY_PRIVATE_ENVIRON" ]; then
    echo "Private variable is NOT FOUND" > /app/check-result
else
    echo "Private variable is FOUND" > /app/check-result
fi

cat /app/check-result
