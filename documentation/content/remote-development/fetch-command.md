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
