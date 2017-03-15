---
title: Bitbucket Integration
menu:
  main:
    parent: 'configuration'
    weight: 100

weight: 100
---
In order to use ContinuousPipe with a Bitbucket repository, you need to configure two interaction points: 

## Creating a Linked Account in ContinuousPipe

The first interaction is linking the Bitbucket account to ContinuousPipe. This is needed when creating a flow so that ContinuousPipe can list repositories to select. 

Setting up a linked account is demonstrated in the Quick Start guide to [creating a flow]({{< relref "quick-start/creating-a-flow.md" >}}).

## Installing the ContinuousPipe Integration in Bitbucket

The second interaction is installing the ContinuousPipe integration in Bitbucket. This is needed in order to let ContinuousPipe know about any repository changes that have been made and to add comments on pull requests.

The integration needs to be installed on the Bitbucket account hosting the repository. Go to the Bitbucket account settings page, and in the left menu click on "Manage integrations".

![](/images/configuration/bitbucket-settings-menu.png)

You can now click on "Install add-on from URL" and enter `https://authenticator.continuouspipe.io/connect/service/bitbucket/addon/` as show in this example:

![](/images/configuration/bitbucket-addon-install.png)

The last step is to grant ContinuousPipe access to the code repository by clicking the "Grant access" button in the following screen.

![](/images/configuration/bitbucket-grant-access-popup.png)

You are now all set and the Bitbucket integration is configured for the code repository.