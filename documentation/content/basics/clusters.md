---
date: 2017-01-25T10:40:26Z
title: Clusters
menu:
  main:
    parent: 'Basics'
    weight: 40
---
ContinuousPipe's deploys effectively on any Kubernetes cluster. That means that you can deploy any application that can run in a Docker container on your bare metal infrastructure or any cloud such as AWS, GCE or even Azure.

[Kubernetes](https://kubernetes.io) is a Docker container orchestration tool written and maintained by Google. It's recognised as one of the best container orchestration platforms around. Google even provides a managed Kubernetes cluster that you can scale by one click, known as [Google Container Engine](https://cloud.google.com/container-engine/).

It's important to know that, by using [configuration variables]({{< relref "configuration-files.md#variables" >}}), you can deploy different environments on different clusters. You can therefore deploy your production environment(s) on an AWS cluster, using AWS cloud-native services, and your UAT (User Acceptance Testing) environments on a Google Container Engine cluster.
