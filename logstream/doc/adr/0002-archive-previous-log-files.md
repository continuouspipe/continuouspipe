# 2. Archive previous log files

Date: 15/05/2016

## Status

Accepted

## Context

Mostly for cost and performances reasons, we can't keep the entire logs in the Firebase instance.

## Decision

Because we are using Google Cloud, we chose to store the entire JSON object as a binary file in the
Google Cloud Storage, mostly because it looks easy and cheap.

## Consequences

The front-end needs the handle the "archived" logs and read them from the bucket.

