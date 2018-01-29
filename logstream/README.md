# LogStream API

This API allows control over the stored logs.

## Endpoints

| Method | Path | Description | Body | Result |
|--------|------|-------------|------|--------|
| *POST* | `/v1/logs` | Creates a new log | The body should be a JSON-encoded _Log_ object. | `200`: Successfully created |
| *PATCH* | `/v1/logs/{logId}` | Patch an existing log | The body should be a JSON-encoded _partial_ _Log_ object. | `200`: Successfully updated |
| *POST* | `/v1/archive/{logId}` | Archive the given log | ø | The archived log |

## Development

1. Add your `firebase.json` service account key in `var/keys/firebase.json`.
2. Run the `main.js` file with supervisor:
```
HTTP_PORT=8000 \
PORT=8001 \
FIREBASE_APP=continuous-pipe \
supervisor main.js
```
