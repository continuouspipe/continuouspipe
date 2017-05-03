---
title: "Command: Delete"
menu:
  main:
    parent: 'remote-development'
    weight: 30

weight: 30

aliases:
    - /remote-development/delete-command/
---
## Using the Logs Command

```
cp-remote delete
```

Delete resources by filenames, stdin, resources and names, or by resources and label selector.

JSON and YAML formats are accepted. Only one type of the arguments may be specified: filenames, resources and names, or resources and label selector.

Some resources, such as pods, support graceful deletion. These resources define a default period before they are forcibly terminated (the grace period) but you may override that value with the --grace-period flag, or pass --now to set a grace-period of 1. Because these resources often represent entities in the cluster, deletion may not be acknowledged immediately. If the node hosting a pod is down or cannot reach the API server, termination may take significantly longer than the grace period. To force delete a resource,  you must pass a grace   period of 0 and specify the --force flag.

IMPORTANT: Force deleting pods does not wait for confirmation that the pod's processes have been terminated, which can leave those processes running until the node detects the deletion and completes graceful deletion. If your processes use shared storage or talk to a remote API and depend on the name of the pod to identify themselves, force deleting those pods may result in multiple processes running on different machines using the same identification which may lead to data corruption or inconsistency. Only force delete pods when you are sure the pod is terminated, or if your application can tolerate multiple copies of the same pod running at once. Also, if you force delete pods the scheduler may place new pods on those nodes before the node has released those resources and causing those pods to be evicted immediately.

Note that the delete command does NOT do resource version checks, so if someone submits an update to a resource right when you submit a delete, their update will be lost along with the rest of the resource.

***

## Command Reference

### Options:

Option | Alias | Default | Description
-------|-------|---------|------------
`--config`                |      |       | Local config file. Default is `.cp-remote-settings.yml` within working directory.
`--kube-environment-name` | `-e` |       | The full remote environment name (e.g. project-key-git-branch).

### Flags:

Flag | Alias | Description
-----|-------|------------
`--all`              |       | To select all the specified resources. |
`--cascade`          |       | If true, cascade the deletion of the resources managed by this resource (e.g. Pods created by a ReplicationController).  Default true. (default true) |
`--force`            |       | Immediate deletion of some resources may result in inconsistency or data loss and requires confirmation. |
`--grace-period`     |       | Period of time in seconds given to the resource to terminate gracefully. Ignored if negative. (default -1) |
`--ignore-not-found` |       | Treat "resource not found" as a successful delete. Defaults to "true" when --all is specified. |
`--now`              |       | If true, resources are signaled for immediate shutdown (same as --grace-period=1). |
`--selector`         | `-l`  | Selector (label query) to filter on. |
`--timeout duration` |       | The length of time to wait before giving up on a delete, zero means determine a timeout from the size of the object |

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