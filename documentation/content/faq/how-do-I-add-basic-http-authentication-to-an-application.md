---
title: "Configuration: How do I add basic HTTP authentication to an application?"
menu:
  main:
    parent: 'faq'
    weight: 50
weight: 50
linkTitle: Basic Authentication
---
Publicly exposed endpoints are accessible to anyone by default. If you are using one of the [ContinuousPipe images]({{< relref "faq/what-are-the-continuous-pipe-images.md" >}}) for [Apache](https://github.com/continuouspipe/dockerfiles/tree/master/php-apache#basic-authentication) or [Nginx](https://github.com/continuouspipe/dockerfiles/tree/master/php-nginx#basic-authentication) then you can enable basic auth using environment variables:

```yaml
tasks:
   # ...
   deployment:
       deploy:
           # ...
           services:
               web:
                   specification:
                       environment_variables:
                           - name: AUTH_HTTP_ENABLED
                             value: true
                           - name: AUTH_HTTP_HTPASSWD
                             value: ${AUTH_HTTP_HTPASSWD}
```

Here the value of `AUTH_HTTP_HTPASSWD` is being passed in as a variable to keep it out of version control, this needs to be set on the [configuration page for the flow]({{< relref "quick-start/configuring-a-flow.md" >}}) in the ContinuousPipe console.
