---
title: "Configuration: How do I create a readiness check when deploying services?"
menu:
  main:
    parent: 'faq'
    weight: 80
weight: 80
linkTitle: Readiness Checks on Services
---
Sometimes a service is not immediately available - it may need to load configuration or import data, taking several minutes before it is ready. In this situation you can define a `readiness_probe` to ensure that the availability of the service is deferred until any setup routines are finished.

Sometimes your container won't be listening on the relevant port by the time the readiness probe runs but you will still want to check that it is functioning. A good idea would be to check that a file can be created in a directory such as /tmp (see varnish example).

```yaml
tasks:
    images:
        # ...
    deployment:
        deploy:
            services:
                web:
                    deployment_strategy:
                        readiness_probe:
                            type: http
                            port: 80
                            path: /
                mysql:
                    deployment_strategy:
                        readiness_probe:
                            type: tcp
                            port: 3306
                varnish:
                    deployment_strategy:
                        readiness_probe:
                            type: exec
                            command:
                                - touch
                                - /tmp/healthy
                            initial_delay_seconds: 5
                            period_seconds: 5
                            success_threshold: 1
                            failure_threshold: 10
```

This configuration defines a readiness check on each of the `web` and `mysql` services. The `web` readiness check probes using HTTP on port 80 and the `mysql` readiness check probes using TCP on port 3306. Until both of these readiness checks receive a 200 status the pod will not receive any traffic through Kubernetes.

The Kubernetes documentation has more information on [readiness probes](https://kubernetes.io/docs/tasks/configure-pod-container/configure-liveness-readiness-probes/#defining-readiness-probes).
