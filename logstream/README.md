# LogStream API

This API allows control over the stored logs.

## Endpoints

| Method | Path | Description | Body | Result |
|--------|------|-------------|------|--------|
| *POST* | `/v1/logs` | Creates a new log | The body should be a JSON-encoded _Log_ object. | `200`: Successfully created |
| *PATCH* | `/v1/logs/{logId}` | Patch an existing log | The body should be a JSON-encoded _partial_ _Log_ object. | `200`: Successfully updated |
| *POST* | `/v1/archive/{logId}` | Archive the given log | ø | The archived log |

## Development

Run the `main.js` file with supervisor:
```
HTTP_PORT=8000 \
PORT=8001 \
FIREBASE_APP=continuous-pipe \
FIREBASE_SERVICE_ACCOUNT_PATH=/path/to/your/service-account.json \
supervisor main.js
```
