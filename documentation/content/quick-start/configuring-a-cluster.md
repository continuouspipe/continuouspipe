---
title: Configuring a Cluster
tags: [ "Cluster", "Team", "Kubernetes" ]
menu:
  main:
    parent: 'quick-start'
    weight: 30

weight: 30
---
Before a Flow can be used it needs to know where to deploy to. This is done in the "Clusters" tab of the Team. You'll be prompted to add a new cluster configuration.

![](/images/quick-start/team-cluster-overview-no-cluster.png)

To add a new cluster configuration, click the “ADD” button in the top right of the interface.

You will then be asked to choose from the following:

* **Google Container Engine** - A Kubernetes cluster associated with your linked Google account
* **My Own Kubernetes Cluster** - A Kubernetes cluster managed elsewhere

## Google Container Engine
**In order to use this option you will first need to set up a Google Cloud project.** You will then need to link your Google account with ContinuousPipe, which is discussed in [creating a team]({{< relref "creating-a-team.md" >}}).

If selected, you will then be asked to enter the following:

* **Google Account** - A linked Google account
* **Google Cloud Project** - A Google Cloud project associated with the Google account
* **GKE Cluster** - A Google Container Engine (GKE) associated with the Google Cloud project

## My Own Kubernetes Cluster 
**In order to use this option you will first need to set up a custom Kubernetes cluster and have access credentials.**

If selected, you will then be asked to enter the following:

* **Cluster Name** - A name for reference within ContinuousPipe
* **Master API Endpoint** - The IP address of the cluster
* **Version** - The Kubernetes version of the cluster
* **Username** - The username to access the cluster
* **Password** - The password to access the cluster

Then click "CREATE" to finish.

![](/images/quick-start/team-cluster-overview.png)