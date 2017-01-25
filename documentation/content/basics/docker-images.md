---
date: 2017-01-25T10:40:21Z
title: Docker Images
---

Every time you need to deploy and/or run tests on your codebase, ContinuousPipe will build and push a Docker image from your code base to any Docker Registry.

Your own image will be stored with its own name and tag, depending of the build strategy you chose. They can be stored in your own Docker Registry, to the famous [Docker Hub](https://hub.docker.com/), or to powerful private repositories such as [quay.io](https://quay.io/).

ContinuousPipe allows you to inject build arguments and share artifacts to download private dependencies or only uses the result of the build operation in a second Docker image.
