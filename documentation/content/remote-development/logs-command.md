---
title: Logs Command
menu:
  main:
    parent: 'remote-development'
    weight: 30

weight: 30
---
## Using the Logs Command

```
cp-remote logs
```

Print the logs for a container in a pod.

***

{{< figure src="/images/remote-development/cp-remote-development-logs.svg" class="remote-development" >}}

## Argument List:

Argument | Alias | Default | Description
---------|-------|---------|------------
`--environment` | `-e` |       | The environment identifier
`--service`     | `-s` |       | The service to use (e.g. web, mysql)
`--follow`      | `-f` |       | Specify if the logs should be streamed
`--previous`    | `-p` | false | Print the logs for the previous instance of the container in a pod exists
`--since`       |      |       | Only return logs newer than a relative duration like 5s, 2m, or 3h. Defaults to all logs. Only one of since-time / since may be used.
`--tail`        |      | -1    | Lines of recent log file to display. Defaults to -1, showing all log lines.
