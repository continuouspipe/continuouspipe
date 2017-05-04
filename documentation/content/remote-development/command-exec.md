---
title: "Command: Exec"
menu:
  main:
    parent: 'remote-development'
    weight: 50

weight: 50

aliases:
    - /remote-development/exec-command/
---
## Using the Exec Command

To execute a command on a container without first getting a bash session use the `exec` command. The command and its arguments need to follow `--`.

```
cp-remote exec -- ls -la
```

This will run the command on the default container specified during setup but you can specify another container to run the command on. For example, if the service you want to connect to is web:

```
cp-remote exec web -- ls -la
```

***

{{< figure src="/images/remote-development/cp-remote-development-exec.svg" class="remote-development" >}}

## Interactive Mode

{{< note title="Note" >}}
You can read a full explanation of interactive mode, including how to get an API key and what arguments are available [here]({{< relref "remote-development/working-with-different-environments.md#interactive-mode" >}}).
{{< /note >}}

Interactive mode allows you to connect directly to an environment that is not set up as a remote environment.

Running the `exec` command in interactive mode looks like this:

```
cp-remote exec --interactive --kube-environment-name php-example-cpdev-foo --service web --flow-id 1268cc54-0c360641bb54 -- /bin/sh
cp-remote exec -i -e php-example-cpdev-foo -s web -f 1268cc54-0c360641bb54 -- /bin/sh
```

***

{{< figure src="/images/remote-development/cp-remote-development-exec-interactive.svg" class="remote-development" >}}

## Command Reference

### Options:

Option | Alias | Default | Description
-------|-------|---------|------------
`--config`                |      |       | Local config file. Default is `.cp-remote-settings.yml` within working directory.
`--flow-id`               |      |       | The flow identifier.
`--kube-environment-name` | `-e` |       | The full remote environment name (e.g. project-key-git-branch).
`--service`               | `-s` | web   | The service to use (e.g. web, mysql).

### Flags:

Flag | Alias | Default | Description
-----|-------|---------|------------
`--interactive` | `-i` | false | Use interactive mode. Interactive mode allows you specify a ContinuousPipe username and API key to run commands without a token. Interactive mode requires the flags `--kube-environment-name`, `--service` and  `--flow-id` to be specified.
