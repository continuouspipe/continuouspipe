---
title: Build Command
menu:
  main:
    parent: 'remote-development'
    weight: 30

weight: 30
---
## Using the Build Command

```
cp-remote build
```

### Creating a New Remote Environment

The `build` command will push changes the branch you have checked out locally to your remote environment branch. ContinuousPipe will then build the environment. You can use the [ContinuousPipe console](https://ui.continuouspipe.io/) to see when the environment has finished building and to find its IP address.

### Rebuilding the Remote Environment

The `build` command is also used to rebuild your remote environment. Assuming you are on the same branch used to create the new remote environment the command will force push the branch which will make ContinuousPipe rebuild the environment. If there has been no commit since the last build an empty commit is automatically made to force the rebuild.

***

{{< figure src="/images/remote-development/cp-remote-development-build.svg" class="remote-development" >}}

## Command Reference

### Options:

Option | Alias | Default | Description
-------|-------|---------|------------
`--config` | | | Local config file. Default is `.cp-remote-settings.yml` within working directory.
