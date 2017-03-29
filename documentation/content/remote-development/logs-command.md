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

- `--environment` or `-e` - the environment identifier
- `--service` or `-s` - the service name (e.g.: web, mysql)
- `--follow` or `-f` - Specify if the logs should be streamed.
- `--previous` or `-p` - If true, print the logs for the previous instance of the container in a pod if it exists.
- `--since` Only return logs newer than a relative duration like 5s, 2m, or 3h. Defaults to all logs. Only one of since-time / since may be used.
- `--tail` Lines of recent log file to display. Defaults to -1, showing all log lines. (default -1)