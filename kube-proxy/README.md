# Kube Proxy

This API is relaying HTTP requests to a Kubernetes cluster.

```
VERB /<flow-uuid>/<cluster-identifier>/<path.../?>*
```

## Authentication

The authentication is made by HTTP basic auth.

### User's authentication token

*Username:* `x-token-auth`
*Password:* the token

### User's API key

*Username:* the user's username
*Password:* the API key
