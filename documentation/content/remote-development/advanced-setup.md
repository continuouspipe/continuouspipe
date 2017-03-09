---
title: Advanced Setup
menu:
  main:
    parent: 'remote-development'
    weight: 160

weight: 160
---
## Usage Logging

Usage stats for the longer running commands (build and resync) can be logged to https://keen.io by providing a write key, project id and event collection name when running the setup command. No stats will be logged if these are not provided.

## AnyBar Notifications

To get a status notification for the longer running commands (watch and resync) on OSX you can install [AnyBar](https://github.com/tonsky/AnyBar) and provide a port number to use for it during the `setup` command.

## Ignoring Files/Directories when Syncing

Files/directories can be excluded from being synced by the `watch`, `resync` and `fetch` commands. This is done by adding the files/directories to ignore to a `.cp-remote-ignore` file in the project root. This uses the standard rsync excludes-from format.
