---
title: BitBucket integration
LinkTitle: BitBucket integration
menu:
  main:
    parent: 'configuration'
    weight: 100

weight: 100
---

In order to use ContinuousPipe with a BitBucket repository, you'll have to install the BitBucket integration on your account.

To do so, go to your BitBucket account settings page. In the left menu, click on *Manage integrations*.

![](/images/configuration/bitbucket-settings-menu.png)

You can now click on *Install add-on from URL* and enter `https://authenticator.continuouspipe.io/connect/service/bitbucket/addon/` as show in this example:

![](/images/configuration/addon-install.png)

The last step is to grant ContinuousPipe access to your code repository by clicking the *Grant access* button in the following screen.

![](/images/configuration/bitbucket-grant-access-popup.png)

You are now all set and the BitBucket integration is configured for your account.
