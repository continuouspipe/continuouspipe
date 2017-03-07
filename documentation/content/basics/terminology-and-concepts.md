---
LinkTitle: Terminology & Concepts
title: Terminology & Concepts
menu:
  main:
    parent: 'basics'
    weight: 11

weight: 11
---

## Technology
### Code Repositories
Code repositories such as Github and Bitbucket are used to store project code. Interaction with these repositories (such as pushing a branch) is used to initiate the build process for an ContinuousPipe environment.

### Docker Registries
Docker registries such as Docker Hub and Quay.io are used to store Docker images that have been built to support the application.

### Kubernetes
Kubernetes clusters are used to run Docker images. ContinuousPipe supports GCE, AWS or Kubernetes clusters on your own infrastructure.

## Concepts
### Projects
A project is used to group together users, code repository access tokens, Docker image registries and Kubernetes deployment clusters.

### Flow
A flow is used to define a code repository. Typically a single flow is all that is needed. However additional flows can easily be added to represent different code repositories or even different components in the same code repository where a micro services architecture is used.

### Tide
A tide is an execution of a flow. Typically this will be triggered when a branch is pushed to a code repository although it can also be triggered manually. The result of a tide is a working environment, ready to test.
