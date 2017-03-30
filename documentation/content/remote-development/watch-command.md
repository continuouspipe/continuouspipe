---
title: Watch Command
menu:
  main:
    parent: 'remote-development'
    weight: 40

weight: 40
---
## Using the Watch Command

```
cp-remote watch
cp-remote wa # alias
```

The `watch` command will sync changes you make locally to a container that's part of the remote environment. This will use the default container specified during setup but you can specify another container to sync with.

For example, if the service you want to sync to is web:

```
cp-remote watch -s web
```

The `watch` command should be left running, it will however need restarting whenever the remote environment is rebuilt using `build`.

To watch a specific remote project path use the `--remote-project-path` flag
```
cp-remote watch --remote-project-path= /app/sub-folder/
```

### Argument List:

Argument | Alias | Default | Description
---------|-------|---------|------------
`--delete`                         |      |       | Delete extraneous files from destination directories
`--dry-run`                        |      |       | Show what will be transferred without executing
`--individual-file-sync-threshold` | `-t` | 10    | Above this threshold any file or folder that is different compared to the local one will be synced
`--kube-environment-name`          | `-e` |       | The full remote environment name (e.g. project-key-git-branch)
`--latency`                        | `-l` | 500   | Sync latency / speed in milli-seconds
`--remote-project-path`            | `-a` | /app/ | The absolute path to the remote project folder
`--rsync-verbose`                  |      |       | Run rsync in verbose mode for debugging
`--service`                        | `-s` |       | The service to use (e.g. web, mysql)
`--yes`                            | `-y` |       | Skip warning
