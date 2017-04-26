---
title: "Command: Bash"
menu:
  main:
    parent: 'remote-development'
    weight: 50

weight: 50

aliases:
    - /remote-development/bash-command/
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

{{< note title="Note" >}}
You can read a full explanation of interactive mode, including how to get an API key and what arguments are available [here]({{< relref "remote-development/working-with-different-environments.md#interactive-mode" >}}).
{{< /note >}}

Interactive mode allows you to connect directly to an environment that is not set up as a remote environment. 

Running the `bash` command in interactive mode looks like this:

```
cp-remote bash --interactive --kube-environment-name php-example-cpdev-foo --service web --flow-id 1268cc54-0c360641bb54
cp-remote bash -i -e php-example-cpdev-foo -s web -f 1268cc54-0c360641bb54
```

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
`--interactive` | `-i` | Use interactive mode. Interactive mode allows you specify a ContinuousPipe username and API key to run commands without a token. Interactive mode requires the flags `--kube-environment-name`, `--service` and  `--flow-id` to be specified.
