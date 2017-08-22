# Message Puller

Pulling messages from Google Pub/Sub and periodically acknowledging them is not a piece of cake in PHP. This Go binary will
pull the messages, start a PHP process for the message and extend their deadline until the process is finished (or timed-out).

```
./puller -google-project-id=[project-id] -service-account=[base64-encoded] -subscription=[subscription-name] -script-path=[path]
```

`puller` will run the script with the following arguments:
1. The base64-encoded message
