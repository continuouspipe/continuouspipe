# Kube Resources Watcher

Watcher for resources in a Kubernetes cluster. This will run in a Kubernetes cluster, listen for events and report the
resource usage per namespace.

## Usage

```
go run main.go
```

### Environment variables

Variable | Description | Required | Example
--- | --- | --- | ----
CLUSTER_INCEPTION | Watcher is using is running inside the Kubernetes cluster to watch | No | `true`
CLUSTER_ADDRESS | The address of the Kubernetes cluster | Yes (if no `CLUSTER_INCEPTION`) | https://api.k8s.my-domain.com
CLUSTER_USERNAME | Username to connect to the Kubernetes cluter | Yes (if no `CLUSTER_INCEPTION`) | admin
CLUSTER_PASSWORD | The password to use to connect to the Kubernetes cluster | Yes (if no `CLUSTER_INCEPTION`) | ø
HTTP_ENDPOINT | HTTP endpoint to which post the resource usages | No | https://api.my-domain.com/usage
HTTP_ENDPOINT_BEARER_TOKEN | The Bearer token to use in the Authorization header | No | ey123.[...]
DEBOUNCE_SECONDS | Minimum seconds between two updates for the same namespace | No | 60 (Default: 1)
