---
date: 2017-01-26T14:09:58Z
title: Waiting statuses
menu:
  main:
    parent: 'configuration'
    weight: 60
---
Sometimes, as part of your deployment pipeline, you'll wait to wait for other GitHub status. For example if you use the static code analyser tool Scrutinizer, you may want to deployment your application only if the static analysis passes.

![GitHub statuses on Pull-Request](/images/github-statuses.png)

In order to achieve that, you can add a new `wait` task in your tasks list:

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
That way, the tide will be failed if the received status from the given 3rd party do not match the expected state, `success` from Scrutinizer in this example.
