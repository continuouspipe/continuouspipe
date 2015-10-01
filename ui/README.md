# River User Interface

This application is an AngularJS application that provides a UI to River API.

## Development

Just run the bootstrap script and it'll use the docker configuration to start the UI.
```
./bootstrap.sh
```

**WARNING:** if you add a new Bower or NPM dependency, you'll need to rebuild the Docker image:
```
docker-compose build && docker-compose restart
```
