---
title: "Tasks: Manual Approval"
menu:
  main:
    parent: 'configuration'
    weight: 70

weight: 70
---

For production platforms in particular, you may want to introduce an approval process before a deployment can take place. For this you can use the `manual_approval` task, which is one of the [inbuilt tasks]({{< relref "configuration/tasks.md#inbuilt-tasks" >}}).

When the `manual_approval` task is added to a list of tasks it will pause the running of a tide until manual approval is given to the tide in the ContinuousPipe console as shown here:
 
![](/images/configuration/flow-manual-approval.png)

In the following example, if a tide is triggered then the `image` task will run automatically as it is first in the sequence. However, when the `wait_product_owner` runs, the tide will be suspended, pending approval. When approval is given, the tide will resume and the `deployment` task will run.

``` yaml
tasks:
    images:
        # ...
    wait_product_owner:
         manual_approval: ~
    deployment:
        # ...
```

The following example demonstrates the usage of `manual_approval` with a [pipeline]({{< relref "configuration/pipelines.md" >}}). The resulting behaviour for the `Production` pipeline will be the same as the sequential task behaviour above:

``` yaml
tasks:
    images:
        # ...
    wait_product_owner:
         manual_approval: ~
    deployment:
        # ...

pipelines:
    - name: Production
      condition: 'code_reference.branch in ["uat", "production"]'
      tasks:
        - images
        - wait_product_owner
        - deployment
    - name: Features
      # ...
```
