# Kube Proxy

This API is relaying HTTP requests to a Kubernetes cluster.

```
VERB /<flow-uuid>/<cluster-identifier>/<path.../?>*
```

The authentication is made by an basic auth.
*Username:* the user's username
*Password:* the API key

