---
title: "Concepts: Build Concepts"
menu:
  main:
    parent: 'basics'
    weight: 17

weight: 17

aliases:
    - /basics/docker-images
---

## Docker

[Docker](https://www.docker.com/) is an application distribution tool that uses containers. A container is effectively a package of the all the code, tools and libraries that an application needs in order to run.

## Images

ContinuousPipe uses Docker to build container images. When a build is run it reads configuration files from your Git repository to create an image that can be deployed to a Kubernetes cluster.

Once built, ContinuousPipe will push the image to a Docker registry where it will be stored with its own name and tag, depending of the build strategy you choose. ContinuousPipe supports all major Docker registries such as [Docker Hub](https://hub.docker.com/) and [Quay](https://quay.io/). 

Your pushed image will be shared publicly by default but can be made private (both Docker Hub and Quay allow private storage). ContinuousPipe also provides a feature that allows separate images to be built sequentially and share artifacts. This allows an initial image to be built using private dependencies, then share the output of the build process with a second image - the second image can then be pushed without exposing any private credentials.  

ContinuousPipe provides [images]({{< relref "faq/what-are-the-continuous-pipe-images.md" >}}) for many common technologies that you may need to use in your application infrastructure. These can be used as they are or as a base to add customisations according to the requirements of your application. The benefit of using ContinuousPipe images are that they have been created according to best practices on security and performance. For example, the Apache and NGINX server images are automatically configured to use HTTPS only websites and install self signed SSL certificate on container start.

ContinuousPipe also provides several [demo sites]({{< relref "faq/what-are-the-continuous-pipe-demo-sites.md" >}}) on Github that provide sample configuration for common PHP frameworks.
