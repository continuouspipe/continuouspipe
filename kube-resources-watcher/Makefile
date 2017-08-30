IMAGE_NAME=quay.io/continuouspipe/kube-resources-watcher
TAG=v0.0.2

all: tests build push

build:
	env GOOS=linux GOARCH=amd64 go build -o ./bin/kube-resources-watcher-linux-amd64 github.com/continuouspipe/continuouspipe/kube-resources-watcher
	docker build --squash -t $(IMAGE_NAME):$(TAG) .

push:
	docker push $(IMAGE_NAME):$(TAG)

tests:
	go test ./watcher...
