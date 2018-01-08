NAME = docker.io/inviqasession/cp-river-base
VERSION = v14

.PHONY: all build push

all: build push

build:
	docker build -t $(NAME):$(VERSION) --rm .

push:
	docker push $(NAME):$(VERSION)
