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

Argument list:

- `--delete` Delete extraneous files from destination directories
- `--dry-run` Show what would have been transferred
- `--individual-file-sync-threshold` or `-t` Above this threshold the watch command will sync any file or folder that is different compared to the local one (default 10)
- `--kube-environment-name` or `-e` The full remote environment name: project-key-git-branch
- `--latency` or `-l` Sync latency / speed in milli-seconds (default 500)
- `--remote-project-path` or `-a` Specify the absolute path to your project folder, by default set to /app/ (default "/app/")
- `--rsync-verbose` Allows to use rsync in verbose mode and debug issues with exclusions
- `--service` or `-s` The service to use (e.g.: web, mysql)
- `--yes` or `-y` Skip warning