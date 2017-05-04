---
title: "Command: Delete"
menu:
  main:
    parent: 'remote-development'
    weight: 95

weight: 95
---
## Using the Delete Command

```
cp-remote delete
```

The `delete` command will delete resources according to a supplied set of criteria. For the most part the command is a wrapper for the [kubectl delete command](https://kubernetes.io/docs/user-guide/kubectl/v1.6/#delete); however the `--filename`, `--include-extended-apis`, `--output` and `--recursive` options are not available. 

### Examples:

```
  # Delete a pod with minimal delay
  kubectl delete pod foo --now

  # Force delete a pod on a dead node
  kubectl delete pod foo --grace-period=0 --force

  # Delete a pod with UID 1234-56-7890-234234-456456.
  kubectl delete pod 1234-56-7890-234234-456456

  # Delete all pods
  kubectl delete pods --all

  # Delete pods and services with same names "baz" and "foo"
  kubectl delete pod,service baz foo

  # Delete pods and services with label name=myLabel.
  kubectl delete pods,services -l name=myLabel
```

***

## Command Reference

### Options:

Option | Alias | Default | Description
-------|-------|---------|------------
`--config`                |      |       | Local config file. Default is `.cp-remote-settings.yml` within working directory.
`--kube-environment-name` | `-e` |       | The full remote environment name (e.g. project-key-git-branch).
`--grace-period`          |      | -1    | Period of time in seconds given to the resource to terminate gracefully. Ignored if negative.
`--selector`              | `-l` |       | Selector (label query) to filter on.
`--timeout duration`      |      | 0s    | The length of time to wait before giving up on a delete, zero means determine a timeout from the size of the object.

### Flags:

Flag | Alias | Default | Description
-----|-------|---------|------------
`--all`              |       | false | To select all the specified resources.
`--cascade`          |       | true  | If true, cascade the deletion of the resources managed by this resource (e.g. pods created by a ReplicationController).
`--force`            |       | false | Immediate deletion of some resources may result in inconsistency or data loss and requires confirmation.
`--ignore-not-found` |       | false | Treat "resource not found" as a successful delete. Defaults to "true" when --all is specified.
`--now`              |       | false | If true, resources are signaled for immediate shutdown (same as --grace-period=1).
