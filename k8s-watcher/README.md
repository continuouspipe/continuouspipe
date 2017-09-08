# Kubernetes Watcher

Watch the logs or events of a given cluster. They will be sent to Firebase.

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
    "logId": "-KUvUFQ80nw5z2yL13CY"
}
```

### Exec into container

Method: `Websocket`
Path: `/flows/<flowUuid>/cluster/<cluster>/<namespace>/pod/<pod>`

## Local development

```
docker-compose run 
```
