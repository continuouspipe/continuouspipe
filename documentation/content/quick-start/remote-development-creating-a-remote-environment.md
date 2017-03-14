---
title: "Remote Development: Creating a Remote Environment"
menu:
  main:
    parent: 'quick-start'
    weight: 100

weight: 100
---
After [configuring your repository]({{< relref "remote-development-configuring-your-repository.md" >}}) you now need to go to the "Dev Environments" tab within the flow.

![](/images/quick-start/flow-dev-environments-no-env.png)

To create a dev environment, just click the “CREATE MY ENVIRONMENT” button in the top right of the interface. 

On the next screen enter a name for the environment (or accept the default), then click "CREATE".

You will then see a screen prompting you to create a token. Before you generate the token you need to enter a branch name (or accept the default). Then click "GENERATE AN INITIALIZATION TOKEN".

![](/images/quick-start/flow-dev-environments-environment-no-token.png)

You will then see a screen displaying a token. This needs to be copied for use with the remote development tool [init command]({{< relref "remote-development/init-command.md" >}}).

![](/images/quick-start/flow-dev-environments-environment-not-started.png)