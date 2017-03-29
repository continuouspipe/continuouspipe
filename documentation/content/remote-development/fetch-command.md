---
title: Fetch Command
menu:
  main:
    parent: 'remote-development'
    weight: 70

weight: 70
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


{{< note title="Note" >}}
You can override any of the `.cp-remote-ignore` settings by adding a `.cp-remote-ignore-fetch` and using it to add or remove pattern entries:

Examples:

exclude `- /exclude/folder/`

include `+ /include/folder/`
{{< /note >}}


Argument list:

- `--dry-run` Show what would have been transferred
- `--file` or `-f` Allows to specify a file that needs to be fetch from the pod
- `--individual-file-sync-threshold` or `-t` Above this threshold the watch command will sync any file or folder that is different compared to the local one (default 10)
- `--kube-environment-name` or `-e` The full remote environment name: project-key-git-branch
- `--latency` or `-l` Sync latency / speed in milli-seconds (default 500)
- `--remote-project-path` or `-a` Specify the absolute path to your project folder, by default set to /app/ (default "/app/")
- `--rsync-verbose` Allows to use rsync in verbose mode and debug issues with exclusions
- `--service` or `-s` The service to use (e.g.: web, mysql)
- `--yes` or `-y` Skip warning