# LogStream API

This API allows control over the stored logs.

## Endpoints

| Method | Path | Description | Body | Result |
|--------|------|-------------|------|--------|
| *POST* | `/v1/logs` | Creates a new log | The body should be a JSON-encoded _Log_ object. | `200`: Successfully created |
| *PATCH* | `/v1/logs/{logId}` | Patch an existing log | The body should be a JSON-encoded _partial_ _Log_ object. | `200`: Successfully updated |


## Development

```
PORT=8001 FIREBASE_APP=continuous-pipe supervisor main.js
```
