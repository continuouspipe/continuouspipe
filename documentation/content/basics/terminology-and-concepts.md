---
LinkTitle: Terminology & Concepts
title: Terminology & Concepts
menu:
  main:
    parent: 'basics'
    weight: 20

weight: 11
---

## System Overview

{{< figure src="/images/basics/cp-system-overview.png" class="diagram system-overview" >}}

## Technology
### Code Repository
Code repositories such as Github and Bitbucket are used to store project code. Interaction with these repositories (such as pushing a branch) is used to initiate the build process for an ContinuousPipe environment.

### Docker Registry
Docker registries such as Docker Hub and Quay.io are used to store Docker images that have been built to support the application.

### Kubernetes
Kubernetes clusters are used to run Docker images. ContinuousPipe supports GCE, AWS or Kubernetes clusters on your own infrastructure.

## Concepts
### Project
A project is used to group together users, code repository access tokens, Docker image registries and Kubernetes deployment clusters.

### Flow
A flow is used to define a code repository. Typically a single flow is all that is needed. However additional flows can easily be added to represent different code repositories or even different components in the same code repository where a micro services architecture is used.

### Tide
A tide is an execution of a flow. Typically this will be triggered when a branch is pushed to a code repository although it can also be triggered manually. The result of a tide is a working environment, ready to test.

#### Tide Triggers with Task Filters

The following diagram shows how a tide can be triggered. Additionally, it shows how [task filters]({{< relref "configuration/tasks.md#filters" >}}) can affect the execution of the tide:

{{< figure src="/images/basics/cp-tide-triggers-default.png" class="tide-triggers-default" >}}

#### Tide Triggers with Pipeline Filters

The following diagram shows an alternative configuration using [pipeline filters]({{< relref "configuration/pipelines.md" >}}):

{{< figure src="/images/basics/cp-tide-triggers-pipeline.png" class="tide-triggers-pipeline" >}}
