# River User Interface

This application is an AngularJS application that provides a UI to River API.

## Development (Docker)

Just run the bootstrap script and it'll use the docker configuration to start the UI.
```
./bootstrap.sh
```

**WARNING:** if you add a new Bower or NPM dependency, you'll need to rebuild the Docker image:
```
docker-compose build && docker-compose restart
```

## Development (Local setup)

The project requires the latest node LTS version. It's recommended to install `nvm` if you haven't already.
Then you can just run the following command and it will set the correct node version.
```
nvm use
```

Once you have node setup correctly you can install the dependencies.
```
npm install
```

You will also need the following additional packages:
```
npm install -g grunt-cli
npm install -g bower
npm install -g compass

bower install
gem install compass
```

Finally you can start the application. It requires 2 environment vairables, the API to authenticate against and the River API url.
```
RIVER_API_URL=https://river-staging.continuouspipe.io grunt serve
```
