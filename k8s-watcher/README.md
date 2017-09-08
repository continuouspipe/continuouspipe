# Kubernetes Watcher

Watch the logs or events of a given cluster. They will be sent to Firebase.

## Environment variables

Name | Required | Description
`FIREBASE_DATABASE_NAME` | Yes | The name of the Firebase database to use for logging. Example: continuouspipe-watch-logs
`FIREBASE_SERVICE_ACCOUNT` | Yes | The base64-encoded service account to authenticate to Firebase
`KUBE_PROXY_HOSTNAME` | Yes | Hostname of the kube-proxy to use

## API

### Watch logs

This will start (or renew) a watcher for the given pod on the given cluster. The logs will be sent to Firebase
and the identifier of the created log will be returned.

Method: `POST`
Path: `/v1/watch/logs`

Request:
```
{
    "cluster": {
        "address": "https://1.2.3.4",
        "credentials": {
            "username": "username",
            "password": "password"
        }
    },
    "namespace": "namespace",
    "pod": "pod"
}
```

Response:
```
200 OK

{
    "identifier": "-KUvUFQ80nw5z2yL13CY",
    "database": {
        "name": "[firebase-database-name]",
        "authentication_token": "TOKEN"
    }
}
```

### Exec into container

Method: `Websocket`
Path: `/flows/<flowUuid>/cluster/<cluster>/<namespace>/pod/<pod>`

## Local development

```
docker-compose run 
```
