---
title: "Command: Logs"
menu:
  main:
    parent: 'remote-development'
    weight: 30

weight: 30

aliases:
    - /remote-development/logs-command/
---
## Using the Logs Command

```
cp-remote logs
```

Print the logs for a container in a pod.

***

{{< figure src="/images/remote-development/cp-remote-development-logs.svg" class="remote-development" >}}

## Command Reference

### Options:

Option | Alias | Default | Description
-------|-------|---------|------------
`--config`                |      |       | Local config file. Default is `.cp-remote-settings.yml` within working directory.
`--kube-environment-name` | `-e` |       | The full remote environment name (e.g. project-key-git-branch).
`--service`               | `-s` | web   | The service to use (e.g. web, mysql).
`--since`                 |      |       | Only return logs newer than a relative duration (e.g. 5s, 2m, 3h). All logs shown by default.
`--tail`                  |      | -1    | Lines of recent log file to display. Defaults to -1, showing all log lines.

### Flags:

Flag | Alias | Default | Description
-----|-------|---------|------------
`--follow`      | `-f` | false | Logs will be streamed.
`--previous`    | `-p` | false | Print the logs for the previous instance of the container if a pod exists.

