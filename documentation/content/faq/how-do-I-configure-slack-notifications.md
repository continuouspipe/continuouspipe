---
title: "Configuration: How do I configure Slack notifications?"
menu:
  main:
    parent: 'faq'
    weight: 100
weight: 100
linkTitle: Slack Notifications
---
You may want to configure ContinuousPipe to keep you updated in Slack on the status of deployments. To do this, first you will need to configure an incoming webhook URL within the Slack application and associate it with a Slack channel. Then, add a `notifications` block to your `continuous-pipe.yml` as follows:

```yaml
tasks:
    images:
        # ...
    deployment:
        # ...

notifications:
    my_notification:
        slack:
            webhook_url: https://hooks.slack.com/services/1/2/3
```

In this default configuration you will then receive notification for each stage of a tide:

- `pending`
- `running`
- `success`
- `failure`

You may want to filter this and (for example) only receive notifications for failed tides. This can be done using the following:

```yaml
tasks:
    images:
        # ...
    deployment:
        # ...

notifications:
    my_notification:
        slack:
            webhook_url: https://hooks.slack.com/services/1/2/3
        when:
            - failure
```

Here the `when` component filters the notifications to just send when a failure occurs.