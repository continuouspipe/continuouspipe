---
title: "Command: Fetch"
menu:
  main:
    parent: 'remote-development'
    weight: 70

weight: 70

aliases:
    - /remote-development/fetch-command/
---
## Using the Fetch Command

```
cp-remote fetch
cp-remote fe # alias
```

When the remote environment is rebuilt it may contain changes that you do not have on the local filesystem.

For example, for a PHP project part of building the remote environment could be installing the vendors using composer. Any new or updated vendors would be on the remote environment but not on the local filesystem which could cause issues, such as autocomplete in your IDE not working correctly. The `fetch` command will copy changes from the remote to the local filesystem. This will resync with the default container specified during setup but you can specify another container.

For example to resync with the `web` container:

```
cp-remote fetch web
```

To specify a specific remote project path use the `--remote-project-path` flag
```
cp-remote fetch --remote-project-path /public/sub-folder/
```

***

{{< figure src="/images/remote-development/cp-remote-development-fetch.svg" class="remote-development" >}}

## Ignoring Files

You can use `.cp-remote-ignore` to [ignore files and directories when syncing]({{< relref "remote-development/advanced-setup.md#ignoring-files-directories-when-syncing" >}}).

This behaviour can be overridden for the `fetch` command by adding a `.cp-remote-ignore-fetch` file. Like `.cp-remote-ignore` it uses standard rsync excludes-from format:

- To exclude use: `- /path/to/folder1/`
- To include use: `+ /path/to/folder2/`

## Sync Threshold

By default the client will synchronise file changes individually up to a certain limit, which by default is 10. Above that threshold a full code scan for changes will be made, which could potentially be slower. If you are editing a large number of files, you should consider upping the default minimum threshold using the `--individual-file-sync-threshold` flag:

```
cp-remote fetch --individual-file-sync-threshold=20
```

## Command Reference

### Options:

Option | Alias | Default | Description
-------|-------|---------|------------
`--config`                         |      |       | Local config file. Default is `.cp-remote-settings.yml` within working directory.
`--file`                           | `-f` |       | Fetch a specific file from the pod.
`--kube-environment-name`          | `-e` |       | The full remote environment name (e.g. project-key-git-branch).
`--remote-project-path`            | `-a` | /app/ | The absolute path to the remote project folder.
`--service`                        | `-s` | web   | The service to use (e.g. web, mysql).

### Flags:

Flag | Alias | Default | Description
-----|-------|---------|------------
`--dry-run`       |      | false | Show what will be transferred without executing.
`--rsync-verbose` |      | false | Run rsync in verbose mode for debugging.
