---
title: "Tasks: Deploying Services"
menu:
  main:
    parent: 'configuration'
    weight: 40

weight: 40
---
Whether you are using a pre-built image or needed to build an image, you can now deploy it. The `deploy` task is configurable in many ways.

The following example assume that you have at least a `web` and a `database` service configured in your `docker-compose.yml` file.

``` yaml
tasks:
    deployment:
        deploy:
            cluster: my-cluster-identifier

            services:
                web: ~
                database: ~
```

For each of these services, you can fine tune a lot of options that are presented in the following paragraphs. Therefore, the full YAML structure won't be presented but assume that every option is under a service configuration like this one:

``` yaml
tasks:
    deployment:
        deploy:
            services:
                database:
                    source:
                        image: mysql
```

## Image Source
If you have a [`build` task]({{< relref "tasks.md" >}}) before the deployment task and an image for the service with the same name was built just before, this image name will be used automatically, so you have nothing to configure.

If that's not the case, the first way to reference an image is to explicitly mention the image name. Note that this value is automatically guessed if you have a service having the same name in your project's `docker-compose.yml` file.

``` yaml
specification:
    source:
        image: mysql
        tag: latest
```

The second way to reference an image, if for instance you are deploying the same image but with different runtime commands, is to use a source from a given service:

``` yaml
specification:
    source:
        from_service: web
```

## Environment Name
You can configure the name of the deployed environment (the namespace in Kubernetes terms) using an expression:

``` yaml
environment:
    name: '"my-app-" ~ code_reference.branch'
```

## Deployment Strategy
The deployment strategy describes how would you like the container(s) to be deployed.

``` yaml
deployment_strategy:
    # If true, the locked parameter ensure that the container(s) won't ever
    # be updated once created
    locked: false

    # If true, an attached container means that ContinuousPipe will wait this container
    # to have finished its job and stream the output
    attached: false

    # If true, the existing container will be forced to be reset
    # at each deployment
    reset: false
```

## Environment Variables
You can set environment variables that will be injected in the running containers.

``` yaml
specification:
    environment_variables:
        - name: VARIABLE_NAME
          value: the-value
        - name: ANOTHER_VALUE
          value: ${USING_A_VARIABLE}
```

## Ports
In order to expose some services to other ones or through a load-balancer, you need to define which ports are exposed by this service.

``` yaml
specification:
    ports:
        - 80
```

{{< note title="Note" >}}
If you have an `expose` configuration in your `docker-compose.yml` file, this configuration will be filled automatically.
{{< /note >}}

## Accessibility
``` yaml
specification:
    accessibility:
        # If true, the service will be accessible from inside the cluster by other services.
        from_cluster: true

        # If true, a public load-balancer will be created for this service.
        from_external: false
```

## Endpoints
In most cases, the `from_external` accessibility value is enough to configure an endpoint. However, if you are using a cluster that supports Ingress and SSL certificates, then you can use the `endpoints` configuration to define these endpoints:

``` yaml
endpoints:
    -
        name: https
        type: NodePort
        ssl_certificates:
            -
                name: your-certificate-name
                cert: ${WILDCARD_SSL_CERT}
                key: ${WILDCARD_SSL_KEY}
```

## Basic HTTP Authentication

If you are using one of the [ContinuousPipe images]({{< relref "faq/what-are-the-continuous-pipe-images.md" >}}) for [Apache](https://github.com/continuouspipe/dockerfiles/tree/master/php-apache#basic-authentication) or [Nginx](https://github.com/continuouspipe/dockerfiles/tree/master/php-nginx#basic-authentication) then you can enable basic auth using environment variables:

``` yaml
specification:
   environment_variables:
       - name: AUTH_HTTP_ENABLED
         value: true
       - name: AUTH_HTTP_HTPASSWD
         value: ${AUTH_HTTP_HTPASSWD}
```

Here the value of `AUTH_HTTP_HTPASSWD` is being passed in as a variable to keep it out of version control, this needs to be set on the [configuration page for the flow]({{< relref "configuring-a-flow.md" >}}) in the ContinuousPipe console.

## Conditional Services
If you need to not deploy some services on a given condition, you can use the `condition` expression:

``` yaml
condition: code_reference.branch not in ["production", "uat", "integration"]
```

## Persistent Volumes
If you want some volumes containing data that will be persistent across the deployments, you can mount some persistent volumes:

``` yaml
specification:
    volumes:
        - type: persistent
          name: my-volume
          capacity: 5Gi
          storage_class: default
    volume_mounts:
        - name: my-volume
          mount_path: /data
```

## Resources
You can define the amount of requested resources, as well as the resource limits for your services. These values will be applied to each replicated container individually.

``` yaml
specification:
    resources:
        requests:
            cpu: 50m
            memory: 250Mi
        limits:
            cpu: 500m
            memory: 500Mi
```

## Health-checks
Health-checks (also called probes) help to identify when a container is ready during a deployment and when a container is still alive when deployed.

``` yaml
deployment_strategy:
    readiness_probe:
        type: tcp
        port: 6379

    liveness_probe:
        initial_delay_seconds: 5
        timeout_seconds: 5
        period_seconds: 5
        type: http
        port: 80
        path: /healthz
```

Sometimes your container won't be listening on the relevant port by the time the readiness probe runs but you will still want to check that it is functioning. A good idea would be to check that a file can be created in a directory such as /tmp.

``` yaml
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

## Manual Approval

A manual approval task can be added to suspend a deployment, pending approval.

Adding the predefined `manual_approval` task will suspend the task sequence until manual approval is given to the tide in the ContinuousPipe console.

``` yaml
tasks:
    images:
        # ...
    wait_product_owner:
         manual_approval: ~
    deployment:
        # ...
```

In this configuration the `images` task will run as normal, but the tide will be suspended when it reaches the `wait_product_owner` task. Once approval has been given the tide will resume and run the `deployment` task.

## Retrieving Deployed Endpoint Addresses

You may have a complex script that contains several `deploy` tasks, each creating their own endpoint. To inform subsequent services about a previous endpoint address ContinuousPipe creates a dynamic variable using the service name. 

``` yaml
tasks:
    infrastructure:
        deploy:
            services:
                backend:
                    # ...

    application:
        deploy:
            services:
                frontend:
                    specification:
                        environment_variables:
                            - name: BACKEND_ENDPOINT
                              value: ${SERVICE_BACKEND_PUBLIC_ENDPOINT}
```

This configuration defines an initial `deploy` task that creates a `backend` service. The second `deploy` task creates a `frontend` service that passes the endpoint address of the `backend` service as an environment variable `BACKEND_ENDPOINT` using the dynamic variable `${SERVICE_BACKEND_PUBLIC_ENDPOINT}` as the value.