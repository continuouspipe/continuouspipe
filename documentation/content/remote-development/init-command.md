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

### Interactive mode

Using the `--interactive` or `-i` you will be requested only the cp username and cp api-key which will be stored in the global configuration file.

```
cp-remote init --interactive 
```

If the credentials are already stored and you want to override them use the `--reset` flag

```
cp-remote init --interactive --reset
```

You will then be able to run the `bash` and `exec` command in interactive mode. Note that step is optional, you can also run directly `cp-remote bash -i` without having to run `cp-remote init -i` first.