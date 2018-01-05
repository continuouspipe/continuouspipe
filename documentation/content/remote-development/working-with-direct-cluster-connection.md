---
title: Working with a Direct Connection to the Cluster
menu:
  main:
    parent: 'remote-development'
    weight: 12

weight: 12
---

Edit the `.cp-remote-settings.yml` local configuration file, set `kube-proxy-enabled` to `false` and add the cluster ip, username and password.

For example:
```
...
kube-proxy-enabled: false
kube-direct-cluster-addr: https://111.222.333.444
kube-direct-cluster-user: {cluster-user}
kube-direct-cluster-password: {cluster-password}
```

After this changes are made, you can simply run any of the command such as `cp-remote bash` and the connection will be made directly to the cluster.