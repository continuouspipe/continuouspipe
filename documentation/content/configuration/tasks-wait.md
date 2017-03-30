---
title: "Tasks: Waiting Statuses"
menu:
  main:
    parent: 'configuration'
    weight: 60

weight: 60

aliases:
    - /configuration/wait-statuses/
---
Sometimes, as part of your deployment pipeline, you'll want to wait for other service integrations to return with a status. For example if you use the static code analyser tool Scrutinizer, you may want to deploy your application only if the static analysis passes.

![GitHub statuses on Pull-Request](/images/github-statuses.png)

In order to achieve that, you can add a new `wait` task to your `tasks` list:

``` yaml
tasks:
    # ...

    wait_scrutinizer:
        wait:
            status:
                context: Scrutinizer
                state: success

    # ...
```

The tide will be failed if the received status from the given third party service does not match the expected state - `success` from Scrutinizer in this example.
