---
title: Release Notes v0.1.3
menu:
  main:
    parent: 'remote-development'
    weight: 170

weight: 170
---

## New Features

* Update to the `watch` command - added rsync pattern matching emulation to limit the excessive amount of times rsync was called. It handles `/` (anchoring), `*`, `**`, `+` and `-`. Any other rsync pattern rules is still valid but will be handled directly by rsync.

* Update to the `init` command - the token argument is persisted in the local configuration file which allows `destroy` and `init` to be run again at a later stage without having to resupply the init token.

* Update to the `bash` and `exec` commands - the `TERM` environment variable will be set on the remote pod shell.

* Update to the `pods` command it will now display the namespace, name, ready, status, restart, age, ip address and cluster node.

## Bug Fixes

* Fix on the windows version of the client - CRLF was not parsed correctly so when the user was asked a question the answer was always invalid.

* Fix in `build` command - once the build was completed the value of `init-status` was not being set to "completed".

* Fix in `init` command - the `--remote-name` flag value was not being saved in the config and therefore was always defaulting to "origin".

* Fix in the logic that determines the target pod - it was not filtering by "Status Running".

## Other Changes

* `checkupdates`, `ckup` will now fetch updates from an AWS S3 bucket, giving faster download speed.