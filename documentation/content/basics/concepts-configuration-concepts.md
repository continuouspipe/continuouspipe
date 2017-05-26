---
title: "Concepts: Configuration Concepts"
menu:
  main:
    parent: 'basics'
    weight: 16

weight: 16
---

## Configuration

ContinuousPipe takes a minimal approach to configuration, and works with existing Docker configuration if already present. All configuration needs to be placed in the code repository root directory:

``` text
.
├── Dockerfile          | https://docs.docker.com/engine/reference/builder/
├── docker-compose.yml  | https://docs.docker.com/compose/compose-file/
└── continuous-pipe.yml | https://docs.continuouspipe.io/configuration/tasks/
```

The `continuous-pipe.yml` file extends `docker-compose.yml`. It allows the ContinuousPipe build and deployment to be configured using a set of tasks expressed using YAML. When a flow is triggered the configuration files are read from the branch that is being acted upon.

## Tasks

Tasks are the central element of ContinuousPipe configuration. By breaking down the configuration into smaller tasks, they can be composed together to create a flexible workflow. The primary tasks handle the configuration of the Docker image build and Kubernetes cluster deployment, however additional tasks can be added to do the following:

- Run arbitrary setup commands
- Wait for the results of other GitHub integrations (e.g. Scrutinizer)
- Require manual approval prior to deployment
- Trigger a webhook
- Trigger notifications of build state to Slack

Tasks can also be conditional so only trigger in response to certain branch name formats or certain pull request labels. By default, tasks will run in declaration sequence, but for more powerful task control pipelines can be defined. These allow different tide execution plans, running only specified tasks in a specified order.

### Tide Triggers with Task Filters

The following diagram shows how a tide can be triggered. Additionally, it shows how [task filters]({{< relref "configuration/tasks.md#filters" >}}) can affect the execution of the tide:

{{< figure src="/images/basics/cp-tide-triggers-default.svg" class="diagram concepts" >}}

### Tide Triggers with Pipeline Filters

The following diagram shows an alternative configuration using [pipeline filters]({{< relref "configuration/pipelines.md" >}}):

{{< figure src="/images/basics/cp-tide-triggers-pipeline.svg" class="diagram concepts" >}}

{{< note title="Note" >}}
It is also possible to add filters to tasks when using pipelines. This creates unnecessary complexity however, so is not recommended.
{{< /note >}}
