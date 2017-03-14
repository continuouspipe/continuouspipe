---
title: "Remote Development: Configuring Your Repository"
menu:
  main:
    parent: 'quick-start'
    weight: 90

weight: 90
---
After [installing the client]({{< relref "remote-development-installing-the-client.md" >}}), the next step is to update the `Dockerfile` so that the container supports remote development.

```
FROM nginx

COPY . /usr/share/nginx/html

RUN apt-get update && apt-get install -y rsync && apt-get clean
RUN ln -s /usr/share/nginx/html /app
```

This update adds two [RUN instructions](https://docs.docker.com/engine/reference/builder/#run). The first installs `rsync` using the `apt-get` package manager. The second adds a symlink "/app" to point to the application code so that the remote client knows where to deploy updates to.

**You will then need to push this update to your code repository.**

{{< note title="Note" >}}
If you are using one of the [ContinuousPipe distributed dockerfiles](https://github.com/continuouspipe/dockerfiles) then `rsync` will already be configured for installation.
{{< /note >}}
