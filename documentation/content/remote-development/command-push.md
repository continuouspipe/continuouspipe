---
title: "Command: Push"
menu:
  main:
    parent: 'remote-development'
    weight: 80

weight: 80

aliases:
    - /remote-development/push-command/
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

## Command Reference

### Options:

Option | Alias | Default | Description
-------|-------|---------|------------
`--config`                         |      |       | Local config file. Default is `.cp-remote-settings.yml` within working directory.
`--file`                           | `-f` |       | Push a specific file to the pod.
`--kube-environment-name`          | `-e` |       | The full remote environment name (e.g. project-key-git-branch).
`--remote-project-path`            | `-a` | /app/ | The absolute path to the remote project folder
`--service`                        | `-s` | web   | The service to use (e.g. web, mysql).

### Flags:

Flag | Alias | Description
-----|-------|------------
`--delete`        |      | Delete extraneous files from destination directories.
`--dry-run`       |      | Show what will be transferred without executing.
`--rsync-verbose` |      | Run rsync in verbose mode for debugging.
`--yes`           | `-y` | Skip warning.