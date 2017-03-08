---
title: Push Command
menu:
  main:
    parent: 'remote-development'
    weight: 90

weight: 90
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