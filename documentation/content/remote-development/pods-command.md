---
title: Pods Command
menu:
  main:
    parent: 'remote-development'
    weight: 120

weight: 120
---
## Using the Pods Command

```
cp-remote pods
cp-remote po # alias
cp-remote checkconnection # alias
cp-remote ck # alias
```

The `pods` command will lists the pods available for the environment

```
cp-remote pods --environment example-remote-branch feature-my-shiny-new-work
```

***

{{< figure src="/images/remote-development/cp-remote-development-pods.svg" class="remote-development" >}}

## Command Reference

### Options:

Option | Alias | Default | Description
-------|-------|---------|------------
`--config`                |      | | Local config file. Default is `.cp-remote-settings.yml` within working directory.
`--kube-environment-name` | `-e` | | The full remote environment name (e.g. project-key-git-branch).
