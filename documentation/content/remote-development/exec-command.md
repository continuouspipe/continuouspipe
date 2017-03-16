---
title: Exec Command
menu:
  main:
    parent: 'remote-development'
    weight: 60

weight: 60
---
## Using the Exec Command

To execute a command on a container without first getting a bash session use the `exec` command. The command and its arguments need to follow `--`.

```
cp-remote exec -- ls -la
```

This will run the command on the default container specified during setup but you can specify another container to run the command on. For example, if the service you want to connect to is web:

```
cp-remote exec web -- ls -la
```

### Interactive mode

With the `--interactive [-i]` flag you can connect directly to a pod that is not setup as a remote environment. 
For this to work, this mode will requires some extra flags to be set: `--environment`, `--service`, `--flow-id`

```
cpr exec --interactive ([-i]) --environment ([-e]) php-example-cpdev-foo --service ([-s]) web --flow-id ([-f]) 1268cc54-0c360641bb54 -- /bin/sh
```