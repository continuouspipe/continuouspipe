---
title: "Tasks: Building Docker Images"
menu:
  main:
    parent: 'configuration'
    weight: 30

weight: 30

aliases:
    - /configuration/images/
---

Before deploying anything, you will most of the time want ContinuousPipe to build your Docker images. For this you need to use the `build` task, which is one of the [inbuilt tasks]({{< relref "configuration/tasks.md#inbuilt-tasks" >}}).

The only mandatory parameter of `build` is the image name you want to build:

``` yaml
tasks:
    images:
        build:
            services:
                web:
                    image: docker.io/your-namespace/your-application
```

You can obviously use any other image name as long as it contains the Docker registry as first part of it. You can also see that it's configured per _service_, here we have only one (named `web`) that we need to deploy.

ContinuousPipe is getting parameters from your `docker-compose.yml` file. The `image` is the only required value if you have such a Docker Compose file.

## Naming Strategy
At the moment, there are two naming strategies: the default one being the `sha1` strategy, which basically builds a tag per commit SHA1. If you require it you can instead use the `branch` strategy, which will create a tag per branch.

``` yaml
tasks:
    images:
        build:
            services:
                web:
                    # ...
                    naming_strategy: branch
```

{{< warning title="Warning" >}}
The branch naming strategy is not recommended, as sometimes when clusters are updated they do not force pull the new image while rolling-updating the services.
{{< /warning >}}

## Arguments

If you need to inject token or strings in your build process in order to download private dependencies for instance, you can use [Docker build arguments](https://docs.docker.com/engine/reference/builder/#/arg).

The following example shows how to install PHP private dependencies (using [Composer](http://getcomposer.org/)) using a private GitHub token:

``` yaml
tasks:
    images:
        build:
            environment:
                - name: GITHUB_TOKEN
                  value: ${GITHUB_TOKEN}

            # ...
```

{{< note title="Note" >}}
This assumes that you have defined the `GITHUB_TOKEN` variable somewhere. You can refer to the [variable section]({{< relref "configuration-files.md#variables" >}}).
{{< /note >}}

The following is an example Docker file to demonstrate how the `GITHUB_TOKEN` will be used during the build:

> **Dockerfile**
```
# ...

ARG GITHUB_TOKEN=

RUN composer config github-oauth.github.com $GITHUB_TOKEN && \
    composer install -o --no-interaction && \
    composer clear-cache
```

If you want to build multiple services at the same time, you can also provide the build argument per service:

``` yaml
tasks:
    images:
        build:
            services:
                api:
                    environment:
                        - name: GITHUB_TOKEN
                          value: ${GITHUB_TOKEN}

            # ...
```

## Artifacts

In order to build small images and/or hide secret values required during the build process, you can use artifacts. These artifacts will allow you to share files or folders between different build steps that use a combination of Dockerfiles, context and build arguments.

For more information see the [artifacts documentation]({{< relref "configuration/artifacts.md" >}}).