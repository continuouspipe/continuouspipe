---
title: Commands
LinkTitle: Running commands
menu:
  main:
    parent: 'configuration'
    weight: 50

weight: 65
---

The `run` task allows to run commands in containers, in the deployed environment context if any. That way, you can run your *integration tests* or your *application migrations* for example.

Most of the options are shared with the [`deploy` task]({{< relref "deployments.md" >}}):

* `cluster`, to select the [cluster]({{< relref "deployments.md#cluster" >}}) on which the container will run
* `image`, to select the [image]({{< relref "deployments.md#image-source" >}}) that will be used to run the commands
* `environment`, to select the [name of the environment]({{< relref "deployments.md#environment-name" >}}) on which the container will be created
* `environment_variables`, to inject some [environment variables]({{< relref "deployments.md#environment-variables" >}})  in the container

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
