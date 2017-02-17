---
title: How do I set up manual approval of deployments?
menu:
  main:
    parent: 'faq'
    weight: 70
linkTitle: Manual Approval
---
You may want to introduce a manual approval step into your deployment process as an added precaution against unintentional or unauthorised deployments. This can be done by adding a `manual_approval` task.

```yaml
tasks:
    images:
        # ...
    deployment:
        # ...

pipelines:
    - name: Production
      condition: 'code_reference.branch in ["uat", "production"]'
      tasks:
        - images
        - manual_approval: ~
        - deployment
    - name: Features
      condition: '"Ready for review" in pull_request.labels'
      tasks: [ images, deployment ]
```

In this configuration the `Features` pipeline will start a Tide which will run the `images` and `deployment` tasks automatically. However, the `Production` pipeline will start a Tide which will run the `images` task, then pause until approval is granted. This is done by clicking on the paused Tide and clicking "APPROVE", which will cause the Tide to resume and run the `deployment` task.

