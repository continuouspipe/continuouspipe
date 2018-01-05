---
title: "Tasks: Running Commands"
menu:
  main:
    parent: 'configuration'
    weight: 50

weight: 60

aliases:
    - /configuration/commands/
---

After deploying you may need to run a series of commands to prepare the deployed container. For instance, you may need to run your integration tests or your application migrations. For this you need to use the `run` task, which is one of the [inbuilt tasks]({{< relref "configuration/tasks.md#inbuilt-tasks" >}}). The `run` task allows you to run commands in containers, inside the deployed environment.

Most of the options are shared with the [`deploy` task]({{< relref "configuration/tasks-deploy.md" >}}):

* `cluster`, to select the [cluster]({{< relref "configuration/tasks-deploy.md#cluster" >}}) on which the container will run
* `image`, to select the [image]({{< relref "configuration/tasks-deploy.md#image-source" >}}) that will be used to run the commands
* `environment`, to select the [name of the environment]({{< relref "configuration/tasks-deploy.md#environment-name" >}}) on which the container will be created
* `environment_variables`, to inject some [environment variables]({{< relref "configuration/tasks-deploy.md#environment-variables" >}})  in the container

Once you've configured these options according to your needs, you only have to define the `commands` options as in the following example.

``` yaml
tasks:
    # ...

    migrations:
        run:
            cluster: my-cluster

            image:
                from_service: web

            commands:
                - composer run-script update-parameters
                - app/console doctrine:migrations:migrate --no-interaction

            environment_variables:
                - name: DATABASE_PASSWORD
                  value: ${THE_PRIVATE_DATABASE_PASSWORD_VARIABLE}
```

That example will run the some migration commands in a container created from the image of the web service. It will run on the cluster named `my-cluster` and will have the environment variable named `DATABASE_PASSWORD` injected with the value of a `THE_PRIVATE_DATABASE_PASSWORD_VARIABLE` [variable previously defined]({{< relref "configuration-files.md#variables" >}}).
