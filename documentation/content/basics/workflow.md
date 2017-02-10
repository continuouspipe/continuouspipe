---
title: A workflow that suits you
LinkTitle: Workflow
menu:
  main:
    parent: 'basics'
    weight: 20

weight: 20
---
Adding a simple feature to a brand’s website should be a piece of cake. But too often the process is hampered by long lead times and slow feedback loops that cost the site owner time and money.

Using ContinuousPipe, you can actually achieve real continuous deployment. The point here is to *reduce the lead time*, meaning the time from when the feature development starts and the time when that given feature goes into production.

![](/images/workflow-before.png)

We believe the typical web development workflow *can be improved* by parallelising the review tasks and automatically deploying small features into production.

![](/images/workflow-with-continuous-pipe.png)

## Integrated with your code repository

GitHub is more than a code repository. It’s also your project repository. Your default branch can be `production` or `uat` so the developers know to which environment they deploy. ContinuousPipe will do the rest with rolling updates to your environment for zero-downtime deployments.

Using [task filters]({{< relref "tasks.md#filters" >}}) you can control the steps ran for a given branch or pull-request. You can even use GitHub's labels to control the tasks that had to be run, or your own custom integration through the hooks tasks.
