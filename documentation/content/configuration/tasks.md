---
title: Task List
LinkTitle: Tasks overview
menu:
  main:
    parent: 'configuration'
    weight: 30

weight: 30
---
As you've seen in the configuration files section, most of ContinuousPipe's configuration is about tasks. These tasks achieve a specific behaviour and can be picked up independently. As of now, here are the 4 official tasks:

* `build`: Will [build and push your Docker image]({{< relref "images.md" >}})
* `deploy`: Will [deploy your service(s) to a cluster]({{< relref "deployments.md" >}})
* `run`: Will run some [commands]({{< relref "commands.md" >}}) inside a deployed context
* `wait`: Will [wait]({{< relref "wait-statuses.md" >}}) for some other GitHub statuses

## Conditional tasks {#filters}
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
  - `branch` which is the name of the branch
  - `sha1` which is the SHA1 of the given commit
* `pull_request` contains the following properties:
  - `labels` an array of the labels on the pull-request.

### Task-related information

Under the `tasks` value, you can have access to information that come from the previous tasks. You need to use named tasks, as in the previous examples, to access to them by their names.

The `deploy` task exposes the following configuration:

* `services` which is an array of objects indexed by key. The key is the name of the deployed service. Then, the objects contains the following property:
  - `created` true if the service was created
  - `updated` true if the service was updated
  - `deleted` true if the service was deleted

For example, if you want to run some setup script only if the database was created you can use the following configuration:

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
