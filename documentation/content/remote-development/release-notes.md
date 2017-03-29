---
title: Release Notes v0.1.2
menu:
  main:
    parent: 'remote-development'
    weight: 170

weight: 170
---

## New Features

* Added `logs` command, check the available flags using `cp-remote logs --help`

* Added `--dry-run` flag for watch, fetch and sync

* Added `--rsync-verbose` flag in watch, fetch and push to help throubleshooting potential issues with ignore files

* Allowing user to create a `.cp-remote-ignore-fetch` which will be included only when doing `fetch`. It allows to override the default `.cp-remote-ignore` file


## Bug Fixes

* Fix in `init` command - when a tide is successful but it skipped the whole build due to filters the environment does not exist and it will now exit with an error

* Fix in `init` command - when a tide failed the process was hanging rather than exiting

## Other Changes

* More descriptive error messages when the CP Api does not respond or it fails to return the data

* Printing version number at the top of the cp-remote log file

* `watch` and `sync` commands won't delete files unless the flag `--delete` is set.