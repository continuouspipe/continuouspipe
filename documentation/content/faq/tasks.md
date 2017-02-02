---
title: Tasks
menu:
  main:
    parent: 'faq'
    weight: 10
---
## TODO:
- [ ] Replace first screenshot with one showing no teams, demonstrating "action prompt"
- [ ] What are the pipelines? (@richard.miller knows + https://github.com/continuouspipe/river/blob/master/features/domain/pipelines/creation.feature)
- [ ] How can I reuse tasks? -> pipeline tasks override (@andy.thompson knows + https://github.com/continuouspipe/river/blob/master/features/domain/pipelines/override.feature)
- [ ] How can I use different environment variables per image build (https://github.com/continuouspipe/river/blob/master/features/domain/tasks/build/context.feature#L42)
- [ ] How to build small Docker images? -> Build steps (I need to finish the feature before)
- [ ] How to generate encrypted variables (I need to finish the feature before)
- [ ] How to configure the Slack notification? (https://github.com/continuouspipe/river/blob/master/features/domain/notifications/slack.feature)
- [ ] Using default to reduce the duplication of configuration (https://github.com/continuouspipe/river/blob/master/features/domain/configuration/default-values/environment.feature)
- [ ] Seek a manual approval in a tide (https://github.com/continuouspipe/river/blob/master/features/domain/tasks/manual_approval/task.feature)
- [ ] How to get the deployed endpoint(s) addresses? (https://github.com/continuouspipe/river/blob/master/features/domain/tasks/deploy/deployed-endpoints-variables.feature)

## Existing JIRA Docs
- pipelines: https://inviqa.atlassian.net/wiki/display/CPROLL/Simplify+ContinuousPipe+configuration+with+pipelines
- env variables: https://inviqa.atlassian.net/wiki/display/CPROLL/Set+environment+values+dependent+on+environment
- defaults: https://inviqa.atlassian.net/wiki/display/CPROLL/Use+defaults+for+configuration+used+by+all+tasks