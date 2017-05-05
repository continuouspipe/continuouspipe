IMAGE_NAME=quay.io/continuouspipe/cloud-builder
TAG=v4

all: build push

build:
	env GOOS=linux GOARCH=amd64 go build -o ./bin/gcb-builder-linux-amd64 github.com/continuouspipe/continuouspipe/gcb-builder
	docker build -t $(IMAGE_NAME):$(TAG) .

push:
	docker push $(IMAGE_NAME):$(TAG)
