IMAGE_NAME=gcr.io/continuous-pipe-1042/cloud-builder
TAG=v8

all: tests build push

build:
	env GOOS=linux GOARCH=amd64 go build -o ./bin/gcb-builder-linux-amd64 github.com/continuouspipe/continuouspipe/gcb-builder
	docker build --squash -t $(IMAGE_NAME):$(TAG) .

push:
	docker push $(IMAGE_NAME):$(TAG)

tests:
	go test ./builder...
