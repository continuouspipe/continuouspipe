---
title: Push Command
menu:
  main:
    parent: 'remote-development'
    weight: 80

weight: 80
---
## Using the Push Command

```
cp-remote push
cp-remote pu #alias
cp-remote sync #alias
cp-remote sy #alias
```

The `push` which will sync files and folders to the remote container.

To specify a specific remote project path use the `--remote-project-path` flag
```
cp-remote push --remote-project-path /public/sub-folder/
```

Argument list:
  
- `--delete` Delete extraneous files from destination directories
- `--dry-run` Show what would have been transferred
- `--file` or `-f` Allows to specify a file that needs to be pushed to the pod
- `--kube-environment-name` or `-e` The full remote environment name: project-key-git-branch
- `--latency` or `-l` Sync latency / speed in milli-seconds (default 500)
- `--remote-project-path` or `-a` Specify the absolute path to your project folder, by default set to /app/ (default "/app/")
- `--rsync-verbose` Allows to use rsync in verbose mode and debug issues with exclusions
- `--service` or `-s` The service to use (e.g.: web, mysql)
- `--yes` or `-y` Skip warning