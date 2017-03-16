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

* Fix in the `build` command as it was not pushing to remote when remote already existed.

* Fix in `init` if an error occurs when git pushes to remote the init process will immediately terminate with an error.

* Fix in `exec` it will now stream the output of the commands. This fix will allow to run an alternative shell if bash does not exist in the remote container by doing `cp-remote exec -- /bin/sh`

* Fix in the `.cp-remote-ignore` since `rsync` does not use `regex` but is own pattern implementation we have replaced `/\.[^/]*$` with `.*`, `\.idea` with `.idea` and `\.git` with `.git`. To more information about the `rsync` patterns refer to the section INCLUDE/EXCLUDE PATTERN RULES in the rsync manual (`man rsync`)

## Other Changes

* Showing all public endpoints after `init` has built the environment