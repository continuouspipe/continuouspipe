---
title: Tasks
menu:
  main:
    parent: 'configuration'
    weight: 20

weight: 20
---
As you've seen in the [configuration files]({{< relref "configuration/configuration-files.md#tasks" >}}), most ContinuousPipe configuration is about tasks. By breaking down the configuration into smaller tasks, they can be composed together to create a flexible workflow. 

## Inbuilt Tasks

ContinuousPipe has several inbuilt tasks that can be included in your workflow:

* `build`: Will [build]({{< relref "configuration/tasks-build.md" >}}) and push your Docker image
* `deploy`: Will [deploy]({{< relref "configuration/tasks-deploy.md" >}}) your service(s) to a cluster
* `run`: Will [run]({{< relref "configuration/tasks-run.md" >}}) some commands inside a deployed context
* `wait`: Will [wait]({{< relref "configuration/tasks-wait.md" >}}) for some other GitHub statuses
* `manual_approval`: Will suspend the task sequence until [manual approval]({{< relref "configuration/tasks-deploy.md#manual-approval" >}}) is given to the tide in the ContinuousPipe console
* `webhook`: Will wait for a response from a third party service

## Conditional Tasks {#filters}

{{< note title="Reference" >}}
Filter expressions use the [Symfony expression language](http://symfony.com/doc/current/components/expression_language/syntax.html).
{{< /note >}}

You can filter the execution of each task by using the `filter` configuration in the YAML file. This can be done with an expression, as you can see in the following sample configuration:

``` yaml
tasks:
    images:
        build:
            # ...

        filter:
            expression: code_reference.branch == 'master'
```

The expression has access to the tide related context objects `code_reference` and `pull_request`.

The `code_reference` context object contains the following properties:

Property | Type | Description
---------|------|------------
`branch` | string | The name of the branch
`sha1`   | string | The SHA1 of the given commit

The `pull_request` context object contains the following properties:

Property | Type | Description
---------|------|------------
`labels` | array | An array of the labels on the pull request
`title`  | string | The title of the pull request
`number` | number | The number of pull requests

## Retrieving Task Information

Within a `tasks` section, you can get access to information that comes from previous tasks. You need to use [named tasks]({{< relref "configuration/configuration-files.md#tasks" >}}) to expose the task information.

The `deploy` task exposes a `services` context object which is an array of objects indexed by key. The key is the name of the deployed service. Each object will contain the following properties:

Property | Type | Description
---------|------|------------
`created` | boolean | If the service was created
`updated` | boolean | If the service was updated
`deleted` | boolean | If the service was deleted

For example, if you want to run a setup script, but only when the database is created, you can use the following configuration:

``` yaml
tasks:
    deployment:
        deploy:
            cluster: my-cluster

            services:
                mysql:
                    specification:
                        source:
                            image: mysql

    fixtures:
        run:
            # ...

        filter:
            expression: tasks.deployment.services.mysql.created
```
