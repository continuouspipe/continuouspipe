---
title: Release Notes v0.1.3
menu:
  main:
    parent: 'remote-development'
    weight: 170

weight: 170
---

## New Features

* Update to the `watch` command - added rsync pattern matching emulation to to limit the excessive amount of times rsync was called. It handles `/` (anchoring), `*`, `**`, `+` and `-`. Any other rsync pattern rules is still valid but will be handled directly by rsync.

* Update to the `init` command - the `token` is persisted in the local configuration file which allows to execute `destroy` and `init` to a later stage without having to re-insert the init token

* Update to the `bash` and `exec` commands - the `TERM` environment variable will be set on the remote pod shell

* Update to the `pods` command it will now display the namespace, name, ready, status, restart, age, ip address and cluster node

## Bug Fixes

* Fix on the windows version - CRLF was not parsed correctly and when the user was asked a question tha answer would have been always invalid 

* Fix in `build` command - once the build was completed the value of `init-status` was not being set to "completed"

* Fix in `init` command - `--remote-name` flag value was not being saved in the config and therefore was always defaulting to origin

* Fix in the logic that determines the target pod - it was not filtering by Status Running

## Other Changes

* `checkupdates`, `ckup` will now fetch updates from an aws s3 bucket which gives faster download speed