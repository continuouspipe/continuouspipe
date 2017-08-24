IMAGE_NAME=gcr.io/continuous-pipe-1042/cloud-builder
TAG=v7

all: tests build

build:
	env GOOS=linux GOARCH=amd64 go build -o ./bin/gcb-builder-linux-amd64 github.com/continuouspipe/continuouspipe/kube-resources-watcher

tests:
	go test ./watcher...
