---
title: How do I use Composer with GitHub personal access token?
menu:
  main:
    parent: 'faq'
    weight: 40
weight: 40
linkTitle: Setup GitHub Token
---
If you need to download private repositories from GitHub during the build process, an access token has to be available during `composer install`.

If you are using one of the [ContinuousPipe dockerfiles](https://github.com/continuouspipe/dockerfiles) then you can pass the image a GitHub token using the `GITHUB_TOKEN` environment variable. During the build process the image will then run `composer global config github-oauth.github.com "$GITHUB_TOKEN"` which will then allow subsequent composer commands permission to access the private repository.

```yaml
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

Here the `api` service is passed `GITHUB_TOKEN` as an environment variable, with the value being passed in as a variable to keep it out of version control. This needs to be set on the [configuration page for the flow]({{< relref "configuring-a-flow.md" >}}) in the ContinuousPipe console.

By setting up these build arguments, the `Dockerfile` will able to consume the `GITHUB_TOKEN` variable using a Docker ARG, as described in the ContinuousPipe image documentation for [Apache](https://github.com/continuouspipe/dockerfiles/tree/master/php-apache#php-70-base) or [Nginx](https://github.com/continuouspipe/dockerfiles/tree/master/php-nginx#php-nginx).

{{< warning title="Warning" >}}
The token can be added as either a Docker build argument or an environment variable. When it is passed as a build argument, the token will be visible in the image history (see `docker history <image-name>` command). When it is given as an environment variable, the token is added to the image itself (see `docker inspect --format '{{ index (index .Config.Env) }}' <image-name>`).

With all of these methods of exposing the token, the final Docker image should be pushed into a private Docker repository to avoid leaking the Github token.
{{< /warning >}}

{{< note title="Note" >}}
When you build the image locally with Docker Compose, then create an environment variable in your shell like `export GITHUB_TOKEN=...`. See the [args section](https://docs.docker.com/compose/compose-file/#args) of Docker Compose file reference for more details.
{{< /note >}}