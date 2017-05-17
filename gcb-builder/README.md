# ContinuousPipe Google Container Builder

This Google Container builder is used by ContinuousPipe, in order to build the Docker images.

## Usage

```
go run main.go
```

## How is it working

This cloud builder will be run by Google Container Builder. It will read a `continuous-pipe.manifest.json` file, download the required
source code, build the docker image, push the docker image, and call an API on ContinuousPipe's Builder API to notify of the success
or failure of the build.

## Testing locally

The only requirement of this Cloud Builder is to be able to talk to a Docker Remote API, as well as reading a manifest file. Therefore,
in order to test the builder locally:

1. Move to a directory containing a `continuous-pipe.manifest.json` file. If you have access to Google Container Builder builds history,
   you can download the source of a previously ran build to have the manifest file.

2. `DOCKER_HOST=unix:///var/run/docker.sock go run $GOPATH/src/github.com/continuouspipe/continuouspipe/gcb-builder/main.go`


