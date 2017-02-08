---
title: Notifications
menu:
  main:
    parent: 'configuration'
    weight: 70

weight: 63
---
ContinuousPipe can send notifications about the tide statuses. This will help the development, QA or product team to have informations about the deployments and the available feature branches.

The notifications are configured in the `notifications` section. All of them can be filter by event, as in the following example.

``` yaml
notifications:
    default:
        github_pull_request: false

    pull_request_on_success:
        github_pull_request: true
        when:
            - success

    slack_everything:
        slack:
            webhook_url: https://...
        when:
            - pending
            - running
            - success
            - failure
```

## GitHub
When using a GitHub repository, two notification types are by default enabled: the commit statues and the pull-request comments.

If you feel too overloaded, you can disable them with the following configuration:

``` yaml
notifications:
    default:
        github_commit_status: false
        github_pull_request: false
```

## Slack
You can send a Slack notification in a Slack webhook. You just have to create a webhook integration into your Slack channel and configure the webhook URL as in the following example:

``` yaml
notifications:
    slack_to_my_organisation:
        slack:
            webhook_url: https://hooks.slack.com/services/[...]/[...]/[...]
```
