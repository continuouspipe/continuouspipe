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

* Fix for `watch` on linux. It was taking too long to add the directory watches.

## Other Changes

* We now send operational metrics to detect and diagnose application errors along with analytics information that will help to improve the tool. For now this metrics affect only the commands `destroy`, `build`, `bash` and `exec`

* For the commands `destroy`, `build`, `bash` and `exec` we are now giving user friendly error messages that aim to explain: what has happened, why has happened and what can be done to resolve along with a session id that can be used to contact support.