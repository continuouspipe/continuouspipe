---
title: Kubernetes Clusters
menu:
  main:
    parent: 'basics'
    weight: 40

weight: 40

aliases:
    - /basics/clusters/
---
ContinuousPipe deploys effectively on any Kubernetes cluster. That means that you can deploy any application that can run in a Docker container on your bare metal infrastructure or any cloud such as AWS, GCE or even Azure.

[Kubernetes](https://kubernetes.io) is a [Docker container](https://www.docker.com/what-container) orchestration tool written and maintained by Google. It's recognised as one of the best container orchestration platforms around. Google even provides a managed Kubernetes cluster that you can scale by one click, known as [Google Container Engine](https://cloud.google.com/container-engine/).

ContinuousPipe even allows you to deploy different environments on different clusters. You can therefore deploy your production environment on an AWS cluster, using AWS cloud-native services, and your UAT (User Acceptance Testing) environments on a Google Container Engine cluster.

When ContinuousPipe deploys an environment to Kubernetes it creates one or more pods. [Kubernetes pods](https://kubernetes.io/docs/user-guide/pods/) are used to group Docker containers together. ContinuousPipe has a flexible YAML configuration syntax that allows you to compose Docker containers and pods together to create an environment. Once deployed, the ContinuousPipe console allows you to view the information Kubernetes reports on the pods, or to access the pods directly if you wish.
