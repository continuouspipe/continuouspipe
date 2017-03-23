---
title: Tasks
menu:
  main:
    parent: 'configuration'
    weight: 20

weight: 20
---
As you've seen in the configuration files section, most of ContinuousPipe's configuration is about tasks. These tasks achieve a specific behaviour and can be picked up independently. As of now, here are the 4 official tasks:

* `build`: Will [build and push your Docker image]({{< relref "images.md" >}})
* `deploy`: Will [deploy your service(s) to a cluster]({{< relref "deployments.md" >}})
* `run`: Will run some [commands]({{< relref "commands.md" >}}) inside a deployed context
* `wait`: Will [wait]({{< relref "wait-statuses.md" >}}) for some other GitHub statuses

{{< note title="Note" >}}
Tasks are run in sequential order, which can be useful if a task should depend on a previous task before execution. For example, ensuring your integration tests are run successfully before deployment. 
{{< /note >}}

## Conditional Tasks {#filters}
You can filter the execution of each task by using the `filter` configuration in the YML file. This can be done with an expression, as you can see in the following sample configuration:

``` yaml
tasks:
    images:
        build:
            # ...

        filter:
            expression: code_reference.branch == 'master'
```

As with the [conditional variables]({{< relref "configuration-files.md#conditional-variables" >}}), this expression has access to a context of objects. The first part of this context is tide-related values:

* `code_reference` that contains the following properties:
  - `branch` - the name of the branch
  - `sha1` - the SHA1 of the given commit
* `pull_request` contains the following properties:
  - `labels` - an array of the labels on the pull request
  - `title` - the title of the pull request

## Retrieving Task Information

Under the `tasks` value, you can get access to information that comes from the previous tasks. You need to use named tasks, as in the previous examples, to access the task information.

The `deploy` task exposes the following configuration:

* `services` which is an array of objects indexed by key. The key is the name of the deployed service. The object will contain the following properties:
  - `created` true if the service was created
  - `updated` true if the service was updated
  - `deleted` true if the service was deleted

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
