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

The `init` command will initialise a remote environment using an initialization token. Tokens can be generated in the ContinuousPipe console when [creating a remote environment]({{< relref "quick-start/remote-development-creating-a-remote-environment.md" >}}).

If the initialization process is interrupted it can be restarted using the same token and by default it will continue the process where it left off. You can use the flag `--reset` to start any partial initializations from the beginning.

```
cp-remote init [token] --reset
```

The `init` command will use the default Git remote name `origin` to create a ContinuousPipe remote development branch. However, if you need to use a different Git remote name you can use the flag `--remote-name`.

```
cp-remote init [token] --remote-name=foo
```

***

{{< figure src="/images/remote-development/cp-remote-development-init.svg" class="remote-development" >}}

## Interactive Mode

Sometimes you may want to connect to an environment that's not set up as a remote environment, e.g. an environment created for a pull request. You can do this using interactive mode. 

You will first need to generate a [ContinuousPipe API key](https://authenticator.continuouspipe.io/account/api-keys).

You can then use interactive mode by using the `--interactive` or `-i` flags.

```
cp-remote init --interactive
cp-remote init -i
```

The first time you run this, you will be asked to enter your ContinuousPipe username and the ContinuousPipe API key you generated. They are then stored in a global configuration file `~/.cp-remote/config.yml` (on linux/osx) `C:\Users\{YourUserName}\.cp-remote\config.yml` (on windows), so you won't need to enter them again.  

You will then be able to run the `bash` and `exec` command within the interactive mode. 

{{< note title="Note" >}}
The [bash command]({{< relref "remote-development/bash-command.md" >}}) and [exec command]({{< relref "remote-development/exec-command.md" >}}) can also be run in interactive mode directly without having to run `cp-remote init -i` first.
{{< /note >}}

If you want to change the stored username and API key, use the `--reset` flag:

```
cp-remote init --interactive --reset
```

***

{{< figure src="/images/remote-development/cp-remote-development-init-interactive.svg" class="remote-development" >}}


## Command Reference

### Arguments:

Argument | Default | Description
---------|---------|------------
`token`  |         | Initialization token. Only needs supplying for first run.

### Options:

Option | Alias | Default | Description
-------|-------|---------|------------
`--config`      | |        | Local config file. Default is `.cp-remote-settings.yml` within working directory.
`--remote-name` | | origin | Remote name of Git repository.

### Flags:

Flag | Alias | Description
-----|-------|------------
`--interactive` | `-i` | Use interactive mode. Interactive mode allows you specify a ContinuousPipe username and api key to run commands without a token.
`--reset`       | `-r` | Start any partial initializations from the beginning.
