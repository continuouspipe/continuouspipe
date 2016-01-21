# LogStream

This is a MeteorJS application that will handle log streams.

## Getting started

To start the application, run this command at the project root:
```
meteor
```

To add new logs by hand to test the real-time update, you can use the MongoDB command line that you can open with the
following command:
```
meteor mongo
```

Then, just insert a new log in the `logs` database:
```
db.logs.insert({text: 'Building application image'});
```

## Start the distribution with Docker

You can start the distribution version with Docker-Compose:
```
docker-compose up
```

**Note:** you won't be able to update code in realtime. If you want to rebuild the image, just run `docker-compose build app`.

## Debugging

If you want debugging messages in the logs, you can set the `ENVIRONMENT` environment variable to `debug`.

## Run tests

1. Start the application with `meteor`
2. In the `tests/cucumber` directory, run:
   ```
   node_modules/chimp/bin/chimp --ddp=http://localhost:3000 --browser=phantomjs
   ```
