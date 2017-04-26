---
title: Bash Command
menu:
  main:
    parent: 'remote-development'
    weight: 50

weight: 50
---
## Using the Bash Command

```
cp-remote bash
cp-remote ba # alias
```

This will remotely connect to a bash session on the default container specified during setup but you can specify another container to connect to. For example, if the service you want to connect to is web:

```
cp-remote bash -s web
```

***

{{< figure src="/images/remote-development/cp-remote-development-bash.svg" class="remote-development" >}}

## Interactive Mode

Interactive mode allows you to connect directly to an environment that is not set up as a remote environment.

If you have not previously run interactive mode with any command, you will first need to generate a [ContinuousPipe API key](https://authenticator.continuouspipe.io/account/api-keys).

You can then use interactive mode by using the `--interactive` or `-i` flags. You will also need to supply the following flags:

- `--kube-environment-name` or `-e` - the environment identifier
- `--service` or `-s` - the service name
- `--flow-id` or `-f` - the flow identifier

The full command looks like this:

```
cp-remote bash --interactive --kube-environment-name php-example-cpdev-foo --service web --flow-id 1268cc54-0c360641bb54
cp-remote bash -i -e php-example-cpdev-foo -s web -f 1268cc54-0c360641bb54
```

If you have not previously run interactive mode with any command, the first time you run this you will be asked to enter your ContinuousPipe username and the ContinuousPipe API key you generated. They are then stored in a global configuration file `~/.cp-remote/config.yml` (on linux/osx) `C:\Users\{YourUserName}\.cp-remote\config.yml` (on windows), so you won't need to enter them again. 

{{< note title="Note" >}}
If you need to reset the stored username and API key, you need to run the [init command]({{< relref "remote-development/init-command.md#interactive-mode" >}}) with the `--reset` flag.
{{< /note >}}

***

{{< figure src="/images/remote-development/cp-remote-development-bash-interactive.svg" class="remote-development" >}}

## Command Reference

### Options:

Option | Alias | Default | Description
-------|-------|---------|------------
`--config`                |      |       | Local config file. Default is `.cp-remote-settings.yml` within working directory.
`--flow-id`               |      |       | The flow identifier.
`--kube-environment-name` | `-e` |       | The full remote environment name (e.g. project-key-git-branch).
`--service`               | `-s` | web   | The service to use (e.g. web, mysql).

### Flags:

Flag | Alias | Description
-----|-------|------------
`--interactive` | `-i` | Use interactive mode. Interactive mode allows you specify a ContinuousPipe username and api key to run commands without a token. Interactive mode requires the flags `--kube-environment-name`, `--service` and  `--flow-id` to be specified.
