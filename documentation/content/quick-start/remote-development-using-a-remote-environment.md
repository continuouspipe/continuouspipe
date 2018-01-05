---
title: "Remote Development: Using a Remote Environment"
menu:
  main:
    parent: 'quick-start'
    weight: 110

weight: 110
---

After [creating a remote environment]({{< relref "remote-development-creating-a-remote-environment.md" >}}) you'll need to open the project directory in a terminal. You can be checked out on any branch. 

{{< note title="Note" >}}
The reason why your local branch name doesn't matter is because `cp-remote` works using a remote branch only. The remote branch it uses is specified in the "Remote Branch Name" field when you generate the token. When `cp-remote init` is executed the remote branch is created automatically and the current HEAD commit is force pushed to it.
{{< /note >}}

The `init` command can then be executed: 

```bash
cd hello-world
cp-remote init <token>
```

Within ContinuousPipe this will trigger the creation of a tide, which becomes in effect the remote development environment. Therefore the running of the `init` command may take several minutes while the tide runs. When it completes it will describe the endpoint associated with the new tide.

![](/images/quick-start/flow-dev-environments-environment-cli-init.png)

Opening the endpoint will reveal the application in its default state.

![](/images/quick-start/flow-dev-environments-environment-view-endpoint.png)

With the tide up and running, it can now be used for remote development. This is done by running the remote development tool [watch command]({{< relref "remote-development/command-watch.md" >}}), which runs in the background, detecting file changes and synchronising them with the remote environment. 

```bash
cp-remote watch
```

Updating a file and saving it will therefore result in an immediate update. 

![](/images/quick-start/flow-dev-environments-environment-cli-watch.png)

If you now refresh the endpoint you will see the changes:

![](/images/quick-start/flow-dev-environments-environment-view-endpoint-change.png)

This allows you to develop locally and view iterative changes remotely in the same way as you would if the environment was on your local machine.