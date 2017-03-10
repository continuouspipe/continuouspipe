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

If the initialization process is interrupted it can be restarted using the same token and by default it will continue the process where it left off. You can use the flag `--reset` to start any partial initializations from the beginning.

```
cp-remote init [token] --reset
```

The `init` command will use the default Git remote name `origin` to create a ContinuousPipe remote development branch. However, if you need to use a different Git remote name you can use the flag `--remote-name`.

```
cp-remote init [token] --remote-name=foo
```
