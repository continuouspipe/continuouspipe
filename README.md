# ContinuousPipe

This repository contains ContinuousPipe's services written in Go.

## Services

- [GCB Builder](gcb-builder/), the cloud builder used to build Docker images on Google Container Builder.
- [Message Puller](message-puller/), a Go Pub/Sub client that will read messages and execute scripts to handle the commands.
- [Kube Resources Watcher](kube-resources-watcher/), a binary to run in a Kubernetes cluster, to report the resources usage.
