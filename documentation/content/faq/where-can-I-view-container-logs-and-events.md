---
title: "Console: Where can I view container logs and events?"
menu:
  main:
    parent: 'faq'
    weight: 9
weight: 9
linkTitle: Deployment Logs and Events
---

## Container Logs

When a container is deployed, Kubernetes generates log and event information, which is useful for debugging when your deployments are failing. ContinuousPipe gives you access to this information in the console without having to access the container directly.

## Viewing the Container Logs From the Tide Log Screen

You can view the container logs and events from the tide log screen of a flow.

First expand the tab for the container (referred to as component) you want to review, then click the hamburger icon of the container in the bottom right corner:

{{< figure src="/images/faq/flow-tide-logs-open-cp-logs.png" class="three-quarter-width" >}}

By default you will see the "LOGS" tab which displays a real time view of the container logs:

{{< figure src="/images/faq/flow-tide-logs-view-cp-logs.png" class="three-quarter-width" >}}

If you click on the "EVENTS" tab you will see a list of events associated with the container deployment:

{{< figure src="/images/faq/flow-tide-logs-view-cp-events.png" class="three-quarter-width" >}}

## Viewing the Container Logs From the Environment Screen

You can also view the container logs and events from the environment screen of a flow.

Click on the environment to expand it, then click the hamburger icon for the container (referred to as component) you want to review:

{{< figure src="/images/faq/flow-environment-open-cp-logs.png" class="three-quarter-width" >}}
