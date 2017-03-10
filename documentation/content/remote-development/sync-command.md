---
title: Sync Command
menu:
  main:
    parent: 'remote-development'
    weight: 90

weight: 90
---
## Using the Sync Command

```
cp-remote sync
cp-remote sy #alias
cp-remote push #alias
cp-remote pu #alias
```

The `sync` which will sync files and folders to the remote container.

To specify a specific remote project path use the `--remote-project-path` flag
```
cp-remote sync --remote-project-path /public/sub-folder/
```