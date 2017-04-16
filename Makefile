IMAGE_NAME=quay.io/continuouspipe/cloud-builder
TAG=v2


build:
	env GOOS=linux GOARCH=amd64 go build -o ./bin/cloud-builder-linux-amd64 github.com/continuouspipe/cloud-builder
	docker build -t $(IMAGE_NAME):$(TAG) .

push:
	docker push $(IMAGE_NAME):$(TAG)
