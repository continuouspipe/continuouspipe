---
title: Init Command
menu:
  main:
    parent: 'remote-development'
    weight: 20

weight: 20
---
## Using the Init Command

```
cp-remote init [token]
```

The `init` command will initialise a remote environment using an authorisation token. Tokens can be generated in the [ContinuousPipe console](https://ui.continuouspipe.io/).

If the initialization process is interrupted it can be restarted using the same token and by default it will continue the process where it left off.
You can use `--reset` to start any partial initializations from the beginning.

Also, the default remote name is `origin` and is used by the `init` command to crate a remote development branch. If you want use a different remote name use the flag `--remote-name`

```
cp-remote init [token] --remote-name=foo --reset
```