---
title: Release Notes v0.1.5
menu:
  main:
    parent: 'remote-development'
    weight: 170

weight: 170
---

## New Features

* Updated `bash` and `exec` commands. When used with `--interactive` and nothing else, it will interactively allow the user to choose the project, flow, environment and pod.

* Updated `bash` and `exec` commands. If the execution interrupts due to the pod being moved it will show an explanatory message.


## Bug Fixes

* Fix for `watch` on Linux. It was taking excessively long to add the directory watches compared to the Mac client due to differences in filesystem handling.

## Other Changes

* We now send operational metrics to detect and diagnose application errors along with analytics information that will help to improve the tool. For now this metrics affect only the commands `destroy`, `build`, `bash` and `exec`.

* For the commands `destroy`, `build`, `bash` and `exec` we are now giving user friendly error messages that aim to explain what has happened, why it has happened, what can be done to resolve, and a session id that can be used to contact support.
