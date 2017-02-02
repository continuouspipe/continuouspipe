---
title: Deployments
LinkTitle: Deploying Services # notice how this overrides what's shown in the menu

menu:
  main:
    parent: 'configuration'
    weight: 40
---
Once your images are built (if you have to build some), you can deploy them. The `deploy` task is configurable in many ways.

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
For each of these services, you can fine-tune a lot of options that are presented in the following paragraphs. Therefore, the full YAML structure won't be presented but assume that every option is under a service configuration, for example:

``` yaml
tasks:
    deployment:
        deploy:
            services:
                database:
                    source:
                        image: mysql
```

## Image source
If you have a [`build` task]({{< relref "tasks.md" >}}) before the deployment task and an image for the service having the same name have been built just before, it'll use this image name, you have nothing to configure.

If it's not the case, the first way is to explicitly mentioning the image name. Note that this value is automatically guessed if you have a service having the same name in your project's `docker-compose.yml` file.

``` yaml
source:
    image: mysql
    tag: latest
```
The last option, if for instance you are deploying the same image but with different runtime commands, you can use a source from a given service:

``` yaml
source:
    from_service: web
```

## Environment name
You can configure the name of the deployed environment (the namespace in Kubernetes terms) using an expression:

``` yaml
environment:
    name: '"my-app-" ~ code_reference.branch'
```

## Deployment strategy
The deployment strategy describe how would you like the container(s) to be deployed.

``` yaml
deployment_strategy:
    # If true, the locked parameter ensure that the container(s) won't never
    #  be updated once created
    locked: false

    # If true, an attached container means that CP will wait this container
    # to have finished its job and stream the output
    attached: false

    # If true, the existing container will be forced to be reset
    # at each deployment
    reset: false
```

## Environment variables
You can set environment variables that are going to be injected in the running containers.

``` yaml
specification:
    environment_variables:
        - name: VARIABLE_NAME
          value: the-value
        - name: ANOTHER_VALUE
          value: ${USING_A_VARIABLE}
```

## Ports
In order to expose some services to other ones or through a load-balancer, you need to precise which ports are exposed by this service.

``` yaml
specification:
    ports:
        - 80
```

Note: if you have an `expose` configuration in your `docker-compose.yml` file, this configuration will be filled automatically.

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
Note: in most cases, the `from_external` accessibility value is enough.

If you are using a cluster that supports Ingress and SSL certificates, then you can use the `endpoints` configuration to define these endpoints:

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

## Conditional services
If you need to not deploy some services on a given condition, you can use the `condition` expression:

``` yaml
condition: code_reference.branch not in ["production", "uat", "integration"]
```

## Persistent volumes
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
You can define the amount of requested resources, as well as the resource limits for your services. This will be applied to only one replica of your container.

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
Health-checks (also called probes) helps to identify when a container is ready during a deployment and when a container is still alive when deployed.

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
