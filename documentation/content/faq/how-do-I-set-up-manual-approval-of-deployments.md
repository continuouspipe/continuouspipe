---
title: How do I set up manual approval of deployments?
menu:
  main:
    parent: 'faq'
    weight: 70
weight: 70
linkTitle: Manual Approval
---
You may want to introduce a manual approval step into your deployment process as an added precaution against unintentional or unauthorised deployments. This can be done by adding a `manual_approval` task.

```yaml
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
      condition: '"Ready for review" in pull_request.labels'
      tasks: [ images, deployment ]
```

In this configuration the `Features` pipeline will start a tide which will run the `images` and `deployment` tasks automatically. However, the `Production` pipeline will start a tide which will run the `images` task, then pause until approval is granted. This is done in the ContinuousPipe console by clicking on the paused tide and clicking "APPROVE", which will cause the tide to resume and run the `deployment` task:

![](/images/configuration/flow-manual-approval.png)
