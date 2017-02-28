---
title: Bash Command
menu:
  main:
    parent: 'remote-development'
    weight: 50

weight: 50
---
## Using the Bash Command

```
cp-remote bash
cp-remote ba # alias
```

This will remotely connect to a bash session on the default container specified during setup but you can specify another container to connect to. For example, if the service you want to connect to is web:

```
cp-remote bash -s web
```
