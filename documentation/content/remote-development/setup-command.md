---
title: Setup Command
menu:
  main:
    parent: 'remote-development'
    weight: 20

weight: 20
---
## Using the Setup Command

To start using the tool for a project, run the `setup` command from the project root.

```
cp-remote setup
```

It will ask a series of questions to get the details for the project set up. The sections below give more detailed information about the questions.

Your answers will be stored in a `.cp-remote-env-settings.yml` file in the project root. You will probably want to add this to your `.gitignore` file.

{{< note title="Note" >}}
Many of the answers are project specific, so it is advisable to provide details of the answers in your project specific README and to securely share sensitive details (such as the cluster password) with team members rather than them rely on the general information provided here.
{{< /note >}}

## Setup Questions

### ContinuousPipe Questions

* **What is your ContinuousPipe project key? (PROJECT_KEY)**

This is the project name used in ContinuousPipe. It will be prefixed to all the environment names created by ContinuousPipe. You can find this on the environments page for the tide on the [ContinuousPipe console](https://ui.continuouspipe.io/). For example:

![Project Key](/images/guides/remote-development/project-key.png)

Here, this is the environment for the develop branch, so the project key is `my-project-develop`.

* **What is the name of the Git branch you are using for your remote environment? (REMOTE_BRANCH)**

The name of the branch you will use for your remote environment. There may be a project specific naming convention for this e.g. `cpdev/<your name>`

* **What is your GitHub remote name? (REMOTE_NAME)**

The name of the git remote for the GitHub project which has the ContinuousPipe integration. In most cases you will have cloned the project repo from this so this will be `origin`.

* **What is the default container for the watch, bash, fetch and resync commands? (DEFAULT_CONTAINER)**

This is an optional setting, if provided this will be used by the `bash`, `watch`, `fetch` and `resync` commands as the container you connect to, watch for file changes, fetch changes from or resync with respectively unless you provide an alternative container to the command. It is the `docker-compose` service name for the container that you need to provide, it may be called something like `web` or `app`.

If you do not provide a default container it will need to be specified every time you use the `bash`, `watch`, `fetch` and `resync` commands.

### AnyBar Questions

* **If you want to use AnyBar, please provide a port number e.g 1738 ? (ANYBAR_PORT)**

This is only needed if you want to get [AnyBar](https://github.com/tonsky/AnyBar) notifications. This will provide a coloured dot in the OSX status bar which will show when syncing activity is taking place. This provides some feedback to know that changes have been synced to the remote development environment.

A value needs to be provided in answer to the question, even if you want to use the default port of 1738, as the notifications are not sent unless a port number is provided.

### Keen.io Questions

* **What is your keen.io write key? (KEEN_WRITE_KEY)**
* **What is your keen.io project id? (KEEN_PROJECT_ID)**
* **What is your keen.io event collection? (KEEN_EVENT_COLLECTION)**

These are only needed if you want to log usage stats using https://keen.io/.

### Kubernetes Questions

* **What is the IP of the cluster?**
* **What is the cluster username?**

The cluster IP address and username can be found on the cluster page for the project in the [ContinuousPipe console](https://ui.continuouspipe.io/):

![Project Key](/images/guides/remote-development/kubernetes-config.png)

* **What is the cluster password?**

The password can be provided by your ContinuousPipe administrator.
