---
title: Check Connection Command
menu:
  main:
    parent: 'remote-development'
    weight: 130

weight: 130
---
## Using the Check Connection Command

```
cp-remote checkconnection
cp-remote ck # alias
cp-remote pods # alias
cp-remote po # alias
```

The `checkconnection` command can be used to check that the connection details for the Kubernetes cluster are correct, and if they are that pods can be found for the environment. It can be used with the namespace option to check another environment:

```
cp-remote checkconnection --environment example-remote-branch feature-my-shiny-new-work
```

***

{{< figure src="/images/remote-development/cp-remote-development-pods.svg" class="remote-development" >}}

## Command Reference

### Options:

Option | Alias | Default | Description
-------|-------|---------|------------
`--config`                |      | | Local config file. Default is `.cp-remote-settings.yml` within working directory.
`--kube-environment-name` | `-e` | | The full remote environment name (e.g. project-key-git-branch).
