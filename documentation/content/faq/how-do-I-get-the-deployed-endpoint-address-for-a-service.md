---
title: How do I get the deployed endpoint address for a service?
menu:
  main:
    parent: 'faq'
    weight: 90
weight: 90
linkTitle: Get Endpoint Address for a Service
---
You may have a complex script that contains several `deploy` tasks, each creating their own endpoint. To inform subsequent services about a previous endpoint address ContinuousPipe creates a dynamic variable using the service name. So, for example, if a service called `api` was created by a `deploy` task, then the endpoint address would become available to subsequent tasks as `${SERVICE_API_PUBLIC_ENDPOINT}`.

```yaml
tasks:
    infrastructure:
        deploy:
            cluster: ${CLUSTER}
            services:
                backend:
                    specification:
                        source:
                            image: ${IMAGE_PATH_BACKEND}

    application:
        deploy:
            cluster: ${CLUSTER}
            services:
                frontend:
                    specification:
                        source:
                            image: ${IMAGE_PATH_FRONTEND}
                        environment_variables:
                            - name: BACKEND_ENDPOINT
                              value: ${SERVICE_BACKEND_PUBLIC_ENDPOINT}
```

This configuration defines an initial `deploy` task that creates a `backend` service. The second `deploy` task creates a `frontend` service that passes the endpoint address of the `backend` service as an enviroment variable `BACKEND_ENDPOINT`.

Additionally, the value of `CLUSTER`, `IMAGE_PATH_BACKEND` and `IMAGE_PATH_FRONTEND` are being passed in as variables - these need to be set on the [configuration page for the flow]({{< relref "configuring-a-flow.md" >}}) in the ContinuousPipe console.
