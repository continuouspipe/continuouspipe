---
title: "Concepts: ContinuousPipe Concepts"
menu:
  main:
    parent: 'basics'
    weight: 15

weight: 15

aliases:
    - /basics/terminology-and-concepts
---

## System Overview

{{< figure src="/images/basics/cp-system-overview.svg" class="diagram concepts" >}}

## Projects

A project is where the ContinuousPipe workflow begins. It is used to group together [Docker registries]({{< relref "basics/concepts-build-concepts.md" >}}) and [Kubernetes clusters]({{< relref "basics/concepts-deployment-concepts.md" >}}), and configure user access. 

Each project needs assigning at least one Docker registry. Storing the Docker registry credentials allows that registry to be specified in the YAML configuration so that ContinuousPipe can automatically push built images. 

Similarly, each project needs assigning at least one Kubernetes cluster. Storing the Kubernetes cluster credentials allows that cluster to be specified in the YAML configuration so that ContinuousPipe can automatically create deployments.

When a project is created the project creator is set as an administrator user. Additional users can then be added as either administrators or basic users by sending them an invitation. Administrator users have extra privileges, such as being able to add new users, change subscription settings, change flow configuration and delete environments.

{{< note title="Note" >}}
ContinuousPipe currently uses GitHub to authenticate users, so a GitHub account is needed in order for a user to access ContinuousPipe. 
{{< /note >}}

## Flows 

A flow is created within a project by selecting a Git code repository. Typically a single flow is all that is needed for a project. However additional flows can easily be added to a project to represent different Git code repositories or even different components in the same Git code repository where a micro services architecture is used.

In parallel with the selection of a Git code repository, [ContinuousPipe integration]({{< relref "basics/installation-github.md" >}}) needs to be installed on that repository so that ContinuousPipe can respond to events such as code pushes, pull requests, and branch deletions.

Each flow allows YAML secrets to be defined and optionally encrypted. This allows sensitive data to be stored within ContinuousPipe rather than exposed in YAML configuration files in the Git code repository. The secrets are exposed to the YAML configuration as variables.

## Tides

A tide is created within a flow. It is triggered automatically by ContinuousPipe when an event occurs within the Git code repository - typically this is either a code push or a pull request. 

The tide then reads the configuration from the branch/commit that instigated the tide. It then runs a sequence of tasks as defined in configuration to build an image and deploy it to a cluster. The result of a tide is a working environment, ready to test.

{{< note title="Note" >}}
A tide can also be triggered manually from within ContinuousPipe.  
{{< /note >}}

## Environments

An environment in ContinuousPipe is a representation of a deployed pod instance within a Kubernetes cluster. ContinuousPipe creates a new environment/pod instance for each branch deployed, creating an identifier based on the branch name. The environment in ContinuousPipe monitors the cluster to make sure it is still healthy, and provides information on the containers within the pod, including log access.

The lifetime of an environment/pod instance is usually in step with the lifetime of the branch, and deleting the branch in Git will trigger the removal of the environment/pod instance in ContinuousPipe/Kubernetes. An environment can be manually deleted, in which case it will be recreated upon the next tide for that branch.

## Remote Development

[Remote development]({{< relref "remote-development/getting-started.md" >}}) in the ContinuousPipe context refers to the use of an environment for the incremental testing of work in development. An environment used in this way is referred to as a "remote development environment" or just "remote environment". A specialised tool `cp-remote` is provided to assist this process, using rsync to synchronise local filesystem changes with the remote environment. This allows local changes to be visible in the remote environment almost instantly so offering the same benefits as a VM but without requiring the same local machine resources. It also offers the benefit of centralising the devops effort needed to support development environments.
