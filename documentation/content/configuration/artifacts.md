---
title: Artifacts
menu:
  main:
    parent: 'configuration'
    weight: 130
weight: 130
---

## Why Use Artifacts?

Artifacts are a ContinuousPipe feature that can be used to solve the problems of:

* **Insecure images** - where an image contains secret values used during the build process
* **Large images** - where an image contains build tools that are not needed beyond the build process

These problems are addressed by introducing build steps to create separate images, and then using artifacts to copy files and folders from an initial build image to a secondary build image. 

For example, you might create an initial build image containing a GitHub access token to pull the contents of a private Git repository, then create a secondary build image and copy the code across. The secondary image would only contain the code and none of the access credentials.

Similarly you might create an initial build image that uses Grunt to build your frontend code, which would need a combination of Ruby, Ruby gems, npm and npm modules to build the code. However, the secondary image would not require these tools once the code was copied across so would be much smaller.

## Using Build Steps

Artifacts are used in conjunction with build steps. The standard way to build an image is to use the following configuration, which is effectively a single build step:

``` yaml
tasks:
    images:
        build:
            services:
                web:
                    image: ${IMAGE_NAME}
```

It defines a Docker image repository location to push the built image. It also assumes that a `Dockerfile` is present in the project root directory to provide instructions on how to build the image. 

Using a build step, the above can also be written as follows:

``` yaml
tasks:
    images:
        build:
            services:
                web:
                    steps:
                        - docker_file_path: ./Dockerfile
                          image: ${IMAGE_NAME} 
```

As you can see, the simple configuration has been replaced with a `steps` section, which explicitly defines where the `Dockerfile` is located as well as defining a Docker image repository location as before.

It is then quite straight forward to add an additional build step: 

``` yaml
tasks:
    images:
        build:
            services:
                web:
                    steps:
                        - docker_file_path: ./Buildfile
                        - docker_file_path: ./Dockerfile
                          image: ${IMAGE_NAME} 
```

This will build two separate Docker images, however as we haven't introduced any artifacts yet the first image will effectively be built and then discarded, so the the net result will be the same as the previous examples.

It is also possible to specify a build directory, which works in conjunction with the Docker file path: 

``` yaml
tasks:
    images:
        build:
            services:
                web:
                    steps:
                        - docker_file_path: ./Buildfile
                          build_directory: ./docker
                        - docker_file_path: ./Dockerfile
                          build_directory: ./docker
                          image: ${IMAGE_NAME} 
```

In this configuration the Docker file paths will be `./docker/Buildfile` and `./docker/Dockerfile` respectively.

## Using Artifacts

To enable artifacts, a `write_artifacts` and `read_artifacts` section needs to be added to separate build steps as follows:

``` yaml
tasks:
    images:
        build:
            services:
                web:
                    steps:
                        - docker_file_path: ./Buildfile
                          write_artifacts:
                              - name: built-files-artifact
                                path: /app/built-files
                        - docker_file_path: ./Dockerfile
                          image: ${IMAGE_NAME}
                          read_artifacts:
                              - name: built-files-artifact
                                path: /built-files
```

The `write_artifacts` section in the first build step creates a write artifact with name "built-files-artifact". It then specifies a path of "/app/built-files" which represents the location where the Docker file `Buildfile` will place the results of the build process that it intends to share. 

The `read_artifacts` section in the second build step creates a corresponding read artifact by referencing the same name as the write artifact i.e. "built-files-artifact". It then specifies a path of "/built-files" which represents the location where the Docker file `Dockerfile` can expect to find the results of the build process that have been shared.

### Docker File for First Build Step

The following is a minimal example Docker file to demonstrate the interaction between the artifact and Docker in the first build step:

> **Buildfile**
```
FROM nginx

RUN mkdir -p /app/built-files && touch /app/built-files/test.txt && echo "test1234" > /app/built-files/test.txt
```

This simply creates a directory that matches the path specified in the write artifact i.e "/app/built-files", and then creates a file within the directory to populate the artifact.

{{< note title="Note" >}}
In practice the contents of the "/app/built-files" directory would be the result of a build process.
{{< /note >}}

### Docker File for Second Build Step

The following is a minimal example Docker file to demonstrate the interaction between the artifact and Docker in the second build step:

> **Dockerfile**
```
FROM nginx

COPY . /usr/share/nginx/html
COPY ./built-files /usr/share/nginx/html
```

This uses the Docker [COPY instruction](https://docs.docker.com/engine/reference/builder/#copy) to import files into the image, copying them to the web server directory. The first COPY instruction imports code from the project repository as normal. The second COPY instruction imports any files shared in the artifact by referencing the path specified in the read artifact prefixed by a full stop i.e "./built-files". In this case there is just one file in the artifact, which will be copied from "./built-files/test.txt" to "/usr/share/nginx/html/test.txt".

## Using Artifacts With Secrets

If your initial build image needs to use secure access credentials (such as an auth token) you can supply it as an environment variable:

``` yaml
tasks:
    images:
        build:
            services:
                web:
                    steps:
                        - docker_file_path: ./Buildfile
                          environment:
                              - name: GITHUB_TOKEN
                                value: ${GITHUB_TOKEN}
                          write_artifacts:
                              - name: built-files-artifact
                                path: /app/built-files
                        - docker_file_path: ./Dockerfile
                          image: ${IMAGE_NAME}
                          read_artifacts:
                              - name: built-files-artifact
                                path: /built-files
```

Here the image in the first build step will be supplied with the enviroment variable `GITHUB_TOKEN`, but the image in the second build step will have no record of it.

The following is an example Docker file to demonstrate how the `GITHUB_TOKEN` will be used in the first build step:

> **Buildfile**
```
FROM nginx

ARG GITHUB_TOKEN=

RUN composer config github-oauth.github.com $GITHUB_TOKEN && \
    composer install -o --no-interaction && \
    composer clear-cache
```

