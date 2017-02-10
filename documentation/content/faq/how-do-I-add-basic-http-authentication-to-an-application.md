---
title: How do I add basic HTTP authentication to an application?
menu:
  main:
    parent: 'faq'
    weight: 50
weight: 50
---

The publicly exposed end points are accessible by anyone by default. If you are using one of the [ContinuousPipe dockerfiles](https://github.com/continuouspipe/dockerfiles) for [Apache](https://github.com/continuouspipe/dockerfiles/tree/master/php-apache#basic-authentication) or [Nginx](https://github.com/continuouspipe/dockerfiles/tree/master/php-nginx#basic-authentication) then you can enable basic auth with environment variables:

```
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

Here the password is being passed in as a variable to keep it out of version control, this needs to be set on the [configuration page for the flow]({{< relref "configuring-a-flow.md" >}}).
