---
title: Configuration Files
menu:
  main:
    parent: 'configuration'
    weight: 10

weight: 10
---

There are three main configuration files that are needed to build a Docker image and set up ContinuousPipe deployments:

```
.
├── Dockerfile
├── continuous-pipe.yml
└── docker-compose.yml
```

## Docker Build Configuration 

The `Dockerfile` contains a series of commands that are combined to build a Docker image. See https://docs.docker.com/engine/reference/builder/ for full documentation.

## Docker Compose Configuration

The file `docker-compose.yml` contains YAML configuration for the services, networks and volumes of a Docker image. See https://docs.docker.com/compose/compose-file/ for full documentation.

## ContinuousPipe Configuration 

The ContinuousPipe configuration is represented as a YAML file. The final configuration is the result of a merger of these different optional configuration sources:

* The YAML stored on CP when [configuring a flow]({{< relref "quick-start/configuring-a-flow.md" >}})
* The YAML file named `continuous-pipe.yml` in your code repository
* The YAML file named `continuous-pipe.[branch].yml` in your code repository

### Tasks

The main objects of this configuration file are the `tasks`. Each task has a name, and will run sequentially.

In the following example, you can see that we define a build task, a deployment task, and a run task, that will operate in this order when a tide is run.

``` yaml
tasks:
    images:
        build:
            # ...

    deployment:
        deploy:
            # ...

    migrations:
        run:
            # ...
```

You will learn more about each task in the [tasks overview section]({{< relref "tasks.md" >}}).

### Variables
You can avoid copying and pasting by using variables inside your configuration files. The following example shows you how to prevent putting values inside your `continuous-pipe.yml` by using variables that are defined in the configuration stored in ContinuousPipe when [configuring a flow]({{< relref "quick-start/configuring-a-flow.md" >}}).

``` yaml
# configuration in ContinuousPipe
environment_variables:
    - { name: CLUSTER, value: my-production-cluster }

# configuration in continuous-pipe.yml
tasks:
    # ...
    deployment:
        deploy:
            cluster: ${CLUSTER}

            # ...
```

Once the configuration files are merged, the variables are resolved. That means that the final configuration will contain the `my-production-cluster` value at the `cluster` key of the deployment task.

### Conditional Variables
Because you may want to change the value of the cluster depending on the branch name for instance, you can use conditions:

``` yaml
environment_variables:
    - { name: CLUSTER, condition: 'code_reference.branch in ["production", "uat"]', value: my-production-cluster }
    - { name: CLUSTER, condition: 'code_reference.branch not in ["production", "uat"]', value: my-development-cluster }
```

As with the [conditional tasks] ({{< relref "tasks.md#filters" >}}), the `condition` value is an expression. In this context, the expression has access to the following variables:

* `code_reference` that contains the following properties:
  - `branch` which is the name of the branch
  - `sha1` which is the SHA1 of the given commit
