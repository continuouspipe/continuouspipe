---
title: GitHub Installation
menu:
  main:
    parent: 'basics'
    weight: 60

weight: 60
---
In order to use ContinuousPipe with a GitHub repository, you need to configure two interaction points: 

## Creating a Connected Account in ContinuousPipe

The first interaction is connecting your GitHub account to ContinuousPipe. This is needed when creating a flow so that ContinuousPipe can list repositories to select. 

Setting up a connected account is demonstrated in the Quick Start guide to [creating a flow]({{< relref "quick-start/creating-a-flow.md" >}}).

## Installing the ContinuousPipe Integration in GitHub

{{< warning title="Warning" >}}
The integration needs to be installed on the GitHub account hosting the repository. If you don't control the account hosting the repository, you will need the account owner to perform this step for you.
{{< /warning >}}

The second interaction is installing the ContinuousPipe integration in GitHub. This is needed in order to let ContinuousPipe know about any repository changes that have been made and to add comments on pull requests.

To install, visit https://github.com/integration/continuouspipe, which will present the installation page. You need to click "Install" to proceed.

{{< note title="Note" >}}
If you get as far as creating a flow without installing the integration, you'll be prompted to do so before you can proceed.
{{< /note >}}

![](/images/basics/github-integration-install.png)

You will then be presented with the configuration page. You can choose whether to apply the installation to all repositories associated with the account with the "All repositories" option or alternatively just limit the integration to individual repositories with the "Only selected repositories" option. Then click "Install" to complete the installation.

![](/images/basics/github-integration-configure.png)

You are now all set and the GitHub integration is configured for the code repository.