---
title: What are the ContinuousPipe images?
menu:
  main:
    parent: 'faq'
    weight: 6
weight: 6
linkTitle: ContinuousPipe Images
---

ContinuousPipe provides images for many common technologies that you may need to use in your application infrastructure. 

Some examples are:

- [PHP with NGINX](https://quay.io/repository/continuouspipe/php7.1-nginx)
- [PHP with Apache](https://quay.io/repository/continuouspipe/php7.1-apache)
- [MYSQL](https://quay.io/repository/continuouspipe/mysql8.0)
- [Redis](https://quay.io/repository/continuouspipe/redis3)
- [Solr](https://quay.io/repository/continuouspipe/solr6)
- [Varnish](https://quay.io/repository/continuouspipe/varnish4)

The full range of images can be seen at https://quay.io/organization/continuouspipe.

The Docker configuration used to create the images can be seen at https://github.com/continuouspipe/dockerfiles.

Any of the images can be used by referencing them in your `Dockerfile`:

```
FROM quay.io/continuouspipe/php7.1-apache:stable
```

The benefit of using ContinuousPipe images are that they have been created according to best practices on security and performance. For example, the Apache and NGINX server images are automatically configured to use HTTPS only websites and install self signed SSL certificate on container start. More information about the specific setup of each image is provided in the README for each image within https://github.com/continuouspipe/dockerfiles.

ContinuousPipe images are also compatible with the [remote development]({{< relref "remote-development/getting-started.md" >}}) functionality without any additional configuration.
