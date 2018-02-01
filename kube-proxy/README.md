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

*Alternative approach:* using the `access_token` query parameter.

### User's API key

*Username:* the user's username
*Password:* the API key

### Local development

```
KUBE_PROXY_LISTEN_ADDRESS=https://localhost:4433 \
KUBE_PROXY_API_URL=http://your-api.continuouspipe.io \
KUBE_PROXY_MASTER_API_KEY=[api-key] \
KUBE_PROXY_INSECURE_SKIP_VERIFY=true \
go run main.go -logtostderr -v 5
```
