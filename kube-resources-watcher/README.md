# Kube Resources Watcher

Watcher for resources in a Kubernetes cluster. This will run in a Kubernetes cluster, listen for events and report the
resource usage per namespace.

## Usage

```
CLUSTER_ADDRESS=https://[...] \
    CLUSTER_USERNAME=admin \
    CLUSTER_PASSWORD=[...] \
     go run main.go
```
