---
title: "Remote Development: Configuring Your Repository"
menu:
  main:
    parent: 'quick-start'
    weight: 90

weight: 90
---
After [installing the client]({{< relref "remote-development-installing-the-client.md" >}}), the next step is to update the configuration files in your code repository.

## Update the Dockerfile

The `Dockerfile` needs to be updated so that the container supports remote development:

```
FROM nginx

COPY . /usr/share/nginx/html

RUN apt-get update && apt-get install -y rsync && apt-get clean
RUN ln -s /usr/share/nginx/html /app
```

This update adds two [RUN instructions](https://docs.docker.com/engine/reference/builder/#run). The first installs `rsync` using the `apt-get` package manager. The second adds a symlink "/app" to point to the application code so that the remote client knows where to deploy updates to.

**You will then need to push this update to your code repository.**

{{< note title="Note" >}}
If you are using one of the [ContinuousPipe images]({{< relref "faq/what-are-the-continuous-pipe-images.md" >}}) then `rsync` will already be configured for installation.
{{< /note >}}

## Update the continuous-pipe.yml

If you are using the `continuous-pipe.yml` configuration from previous steps in the quick start guide, there is no further configuration required so you can go ahead and [create a remote environment]({{< relref "quick-start/remote-development-creating-a-remote-environment.md" >}}).

However, if you have progressed to adding pipelines to your configuration you will need to ensure that the pipeline conditions are not so restrictive that they prevent a tide running for a remote development branch. Remote development branches are by convention in the format "cpdev*".

The following pipeline configuration will allow "cpdev*" branch pushes to trigger a tide:

```
tasks:
    images:
        build:
            services:
                web:
                    image: docker.io/pswaine/hello-world

    deployment:
        deploy:
            cluster: hello-world
            services:
                web:
                    specification:
                        accessibility:
                            from_external: true
                            
pipelines:
    - name: Production
      condition: 'code_reference.branch in ["uat", "production"]'
    - name: Remote
      condition: 'code_reference.branch matches "/^cpdev/"'
```

**You will then need to push this update to your code repository.**
