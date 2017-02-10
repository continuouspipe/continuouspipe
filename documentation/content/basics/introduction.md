---
LinkTitle: Introduction # notice how this overrides what's shown in the menu
title: Introduction to ContinuousPipe
menu:
  main:
    identifier: basics-introduction
    parent: 'Basics'
    weight: 10

weight: 100
---
ContinuousPipe is a system for continuous automated deployment of multiple environments with simple configuration. Utilising Docker and Kubernetes to automatically deploy new environments, it dramatically speeds up your feedback loop and cuts lead time.

![](/images/cp-overview.png)

Built on Docker and Kubernetes you can deploy your [Docker compatible application]({{< relref "docker-images.md" >}}) on [any cluster]({{< relref "clusters.md" >}}) – your own infrastructure, for example, or on any cloud such as AWS or GCE.

The [customizable workflow]({{< relref "workflow.md" >}}) allows you to integrate developers, QA and product owners to produce super-fast feedback cycles on a feature-by-feature basis.
