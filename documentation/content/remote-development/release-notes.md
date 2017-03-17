---
title: Release Notes v0.1.1
menu:
  main:
    parent: 'remote-development'
    weight: 170

weight: 170
---

## New Features

* Added `--interactive [-i]` flag to the `bash`, `exec` and `init` commands

* The list of the public endpoints in shown in the commands `watch [-wa]`, `checkconnection [-ck]` and `pods [-po]`

## Bug Fixes

* Fix in `build` comand - it was not pushing to remote when remote already existed.

* Fix in `init` command - if an error occurs when git pushes to remote the init process will immediately terminate with an error.

* Fix in `exec` command - it will now stream the output of the commands. This fix will allow an alternative shell to be used if bash does not exist in the remote container by running `cp-remote exec -- /bin/sh`

* Fix in `.cp-remote-ignore` - `rsync` uses its own pattern implementation rather than `regex` so we have replaced `/\.[^/]*$` with `.*`, `\.idea` with `.idea` and `\.git` with `.git`. For more information about the `rsync` patterns refer to the section INCLUDE/EXCLUDE PATTERN RULES in the rsync manual (`man rsync`)

## Other Changes

* Showing all public endpoints after `init` has built the environment