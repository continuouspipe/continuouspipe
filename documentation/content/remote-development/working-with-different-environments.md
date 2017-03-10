---
title: Working with a Different Environment
menu:
  main:
    parent: 'remote-development'
    weight: 140

weight: 140
---
## Working with a Different Environment

The `--environment|-e` option can be used with the `watch`, `bash`, `fetch`, `sync`, `checkconnection`, `exec` and `forward` commands to run them against a different environment than the one specified during setup. This is useful if you need to access a different environment such as a feature branch environment. For example, to open a bash session on the `web` container of the `example-feature-my-shiny-new-work` environment you can run:

```
cp-remote bash --environment example-remote-branch feature-my-shiny-new-work -s web
```

or

```
cp-remote bash -e example-feature-my-shiny-new-work -s web
```
