---
title: Configuring a Registry
tags: [ "Registry", "Team", "Docker" ]
menu:
  main:
    parent: 'quick-start'
    weight: 40

weight: 77
---
Before a Flow can be used it needs to know where to store Docker images. This is done in the "Registries" tab of the Team. You'll be prompted to add a new registry configuration.

![](/images/quick-start/team-registry-overview-no-registry.png)

To add a new registry configuration, click the “ADD” button in the top right of the interface.

You will then be asked to choose from the following:

* **Docker Hub Registry**
* **Quay.io Registry**
* **Custom Registry**

**For each of these options you will first need to have set up an account with the respective service.** You will then need to supply the following credentials: 

* **Username** - The username to access the registry
* **Email** - The email address to access the registry
* **Password** - The password to access the registry

Then click "CREATE" to finish.

![](/images/quick-start/team-registry-overview.png)