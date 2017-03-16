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

### Interactive mode

With the `--interactive [-i]` flag you can connect directly to a pod that is not setup as a remote environment. 
For this to work, this mode will requires some extra flags to be set: `--environment`, `--service`, `--flow-id`

```
cpr bash --interactive ([-i]) --environment ([-e]) php-example-cpdev-foo --service ([-s]) web --flow-id ([-f]) 1268cc54-0c360641bb54
```