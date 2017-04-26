---
title: "Command: Watch"
menu:
  main:
    parent: 'remote-development'
    weight: 40

weight: 40

aliases:
    - /remote-development/watch-command/
---
## Using the Watch Command

```
cp-remote watch
cp-remote wa # alias
```

The `watch` command will sync changes you make locally to a container that's part of the remote environment. 

This will use the default container specified during setup but you can specify another container to sync with. For example, if the service you want to sync to is web:

```
cp-remote watch -s web
```

The `watch` command should be left running, it will however need restarting whenever the remote environment is rebuilt using `build`.

To watch a specific remote project path use the `--remote-project-path` flag:

```
cp-remote watch --remote-project-path= /app/sub-folder/
```

***

{{< figure src="/images/remote-development/cp-remote-development-watch.svg" class="remote-development" >}}


## Sync Threshold

By default the client will synchronise file changes individually up to a certain limit, which by default is 10. Above that threshold a full code scan for changes will be made, which could potentially be slower. If you are editing a large number of files, you should consider upping the default minimum threshold using the `--individual-file-sync-threshold` flag:

```
cp-remote watch --individual-file-sync-threshold=20
```

## Command Reference

### Options:

Option | Alias | Default | Description
-------|-------|---------|------------
`--config`                         |      |       | Local config file. Default is `.cp-remote-settings.yml` within working directory.
`--individual-file-sync-threshold` | `-t` | 10    | Above this threshold a full code scan for file changes will be made rather than syncing individual files
`--kube-environment-name`          | `-e` |       | The full remote environment name (e.g. project-key-git-branch).
`--latency`                        | `-l` | 500   | Sync latency / speed in milli-seconds
`--remote-project-path`            | `-a` | /app/ | The absolute path to the remote project folder.
`--service`                        | `-s` | web   | The service to use (e.g. web, mysql).

### Flags:

Flag | Alias | Description
-----|-------|------------
`--delete`        |      | Delete extraneous files from destination directories.
`--dry-run`       |      | Show what will be transferred without executing.
`--rsync-verbose` |      | Run rsync in verbose mode for debugging.
`--yes`           | `-y` | Skip warning.
