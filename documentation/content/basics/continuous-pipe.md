---
LinkTitle: ContinuousPipe
title: ContinuousPipe
menu:
  main:
    parent: 'basics'
    weight: 10

weight: 10
---

## What is ContinuousPipe?

ContinuousPipe is a system for continuous automated deployment of multiple environments with simple configuration. It utilises Docker and Kubernetes to automatically deploy new environments, dramatically speeding up your feedback loop and cutting lead time.

## Technology Stack

ContinuousPipe allows you to deploy your [Docker application]({{< relref "basics/docker-images.md" >}}) on any [Kubernetes cluster]({{< relref "basics/kubernetes-clusters.md" >}}).

{{< figure src="/images/basics/cp-technology-stack.png" class="diagram technology-stack" >}}

## Getting Started

To get started with ContinuousPipe you will need the following:

- A [GitHub](https://github.com/) or [Bitbucket](https://bitbucket.org/) account to host your project
- A Docker registry account e.g. [docker.io](https://docker.io) or [quay.io](https://quay.io)
- A Kubernetes cluster e.g. [GCE](https://cloud.google.com/container-engine/), [AWS](https://aws.amazon.com/) or [Azure](https://azure.microsoft.com/en-au/)

## Customizable Workflow

The [customizable workflow]({{< relref "basics/workflow.md" >}}) allows you to integrate developers, QA and product owners to produce super-fast feedback cycles on a feature-by-feature basis.
