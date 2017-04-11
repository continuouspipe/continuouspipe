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

The `push` command will sync files and folders to the remote container.

To specify a specific remote project path use the `--remote-project-path` flag
```
cp-remote push --remote-project-path /public/sub-folder/
```

***

{{< figure src="/images/remote-development/cp-remote-development-push.svg" class="remote-development" >}}

## Argument List:

Argument | Alias | Default | Description
---------|-------|---------|------------
`--delete`                         |      |       | Delete extraneous files from destination directories
`--dry-run`                        |      |       | Show what will be transferred without executing
`--file`                           | `-f` |       | Fetch a specific file from the pod
`--kube-environment-name`          | `-e` |       | The full remote environment name (e.g. project-key-git-branch)
`--latency`                        | `-l` | 500   | Sync latency / speed in milli-seconds
`--remote-project-path`            | `-a` | /app/ | The absolute path to the remote project folder
`--rsync-verbose`                  |      |       | Run rsync in verbose mode for debugging
`--service`                        | `-s` |       | The service to use (e.g. web, mysql)
`--yes`                            | `-y` |       | Skip warning