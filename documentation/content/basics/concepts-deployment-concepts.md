---
title: "Concepts: Deployment Concepts"
menu:
  main:
    parent: 'basics'
    weight: 18

weight: 18

aliases:
    - /basics/kubernetes-clusters
---

## Kubernetes

[Kubernetes](https://kubernetes.io) is a Docker container orchestration tool written and maintained by Google. It is recognised as one of the best container orchestration platforms around. 

## Clusters

A Kubernetes cluster is a collection of computers (nodes) that work together as a single system. Each node can be either a virtual or physical machine. 

ContinuousPipe supports Kubernetes clusters on all major cloud providers such as [GCE](https://cloud.google.com/container-engine/), [AWS](https://aws.amazon.com/), [Azure](https://azure.microsoft.com/en-au/). You may also use Kubernetes clusters on your own infrastructure. ContinuousPipe even allows you to deploy different environments on different clusters. You can therefore deploy your production environment on an AWS cluster, and your UAT environments on a GCE cluster.

## Pods

A [pod](https://kubernetes.io/docs/concepts/workloads/pods/pod-overview/) is the Kubernetes name for one or more containers deployed to the same host. Each pod is assigned a unique IP address within the cluster. A pod can contain a single container, or several containers that interoperate and are dependent upon each other. For example a web application might have a container for the main application running a web server, a container for the database, and a container for caching (e.g. running Redis).

Pods are intended to be immutable i.e. they should not be reconfigured while they are running. ContinuousPipe supports this by performing rolling update deployments when an application has been modified, so effectively the pod is replaced.

## Services

A [service](https://kubernetes.io/docs/concepts/services-networking/service/) is the Kubernetes name for a grouping of pods. Services act as a load balancer and allow external IPs to be created to access a pod.

{{< note title="Note" >}}
A Kubernetes service is not the same as a ContinuousPipe service. ContinuousPipe services represent the YAML instructions for creating individual containers. This ambiguity will be addressed in a future release of ContinuousPipe.
{{< /note >}}

## Ingress

An [ingress](https://kubernetes.io/docs/concepts/services-networking/ingress/#what-is-ingress) is the Kubernetes name for an entry point for inbound connections accessing a cluster. At its simplest it can be a dynamic IP address directing inbound connections to a single service, but can be a static IP address configured to direct inbound connections to multiple services.
