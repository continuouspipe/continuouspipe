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

The main objects of this configuration file are the `tasks`. 

Each task has a name, so will sometimes be referred to as a "named task". Tasks will run sequentially in the order they are defined.

{{< warning title="Warning" >}}
It is recommended that all tasks are named as some features rely on this to make task information available to other tasks. 
{{< /warning >}}

In the following example, you can see that we define `build`, `deploy`, and `run` tasks named `images`, `deployment`, and `migrations` respectively. They will operate in this order when a tide is run.

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

{{< note title="Reference" >}}
Conditions use the [Symfony expression language](http://symfony.com/doc/current/components/expression_language/syntax.html).
{{< /note >}}

If you need to change the value of the cluster, for instance depending on the branch name, you can use conditions:

``` yaml
environment_variables:
    - { name: CLUSTER, condition: 'code_reference.branch in ["production", "uat"]', value: my-production-cluster }
    - { name: CLUSTER, condition: 'code_reference.branch not in ["production", "uat"]', value: my-development-cluster }
```

The `condition` value is an expression. It has access to the tide related context object `code_reference`.

The `code_reference` context object contains the following properties:

Property | Type | Description
---------|------|------------
`branch` | string | The name of the branch
`sha1`   | string | The SHA1 of the given commit

### Default Variables

You can use a `defaults` section to avoid variable duplication across tasks.

The following example shows a `cluster` variable being defined in two separate tasks:

``` yaml
tasks:
    initialise:
        run:
            cluster: my-cluster
            # ...
   deployments:
        deploy:
            cluster: my-cluster
            # ...
```

This can be rewritten using a `defaults` section as follows:

``` yaml
defaults:
     cluster: my-cluster

tasks:
    initialise:
        run:
            # ...
    deployments:
        deploy:
            # ...
```

The default cluster variable will now be used for both tasks instead.
