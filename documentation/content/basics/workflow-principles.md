---
title: Workflow Principles
LinkTitle: Workflow Principles
menu:
  main:
    parent: 'basics'
    weight: 50

weight: 50

aliases:
    - /basics/workflow/
---

## Workflow Principles

ContinuousPipe does not mandate a specific project workflow. Instead it promotes a series of workflow principles that are intended to improve an existing project workflow or shape a new one. These principles are particularly beneficial when they are used in tandem with agile practices, as they focus on iteration and collaboration.

Used together or just individually, the principles aim to achieve "continuous deployment", reducing the lead time from when a feature starts development to when it goes into production.

### Principle #1: Use GitHub to Control Your Workflow

GitHub offers a range of features that promote the utility of Git beyond that of a simple code repository. You may already be using some of these in your projects. One of the key features is the pull request (PR), which is used to invite code review and collaboration. Another key feature is automatic integration testing when a pull request is created or updated.

ContinuousPipe takes pull requests and automatic integration testing a step further by allowing pull requests to be configured to automatically create an isolated environment to test the branch changes in. This can be further refined to respond only to certain branch name patterns or certain pull request labels. 

The results of the enviroment build, including an IP address to access the enviroment, will be subsequently posted back to the pull request by ContinuousPipe. This gives developers, quality assurance (QA), and product owner (PO) the facility to test a feature within minutes of the pull request being created. 

### Principle #2: Shorten Feedback Loop Between Development and Approval

The results of initial and subsequent builds can be broadcast through several channels. By default GitHub notifications are used, which most developers will already be using. To inform the wider project team, Slack notifications can also be configured. If you are not using Slack then webhooks allow you to customise the way you recieve notifications. This means that the time between "dev complete" and "in testing" can be minimised, and QAs and POs can get to work quickly.

Once an environment has been built and is in review, it may need rework. As soon as the developer has pushed their changes, the environment will be automatically rebuilt and notifications raised. This means that QAs and POs are instantly updated and aware that they can retest the work.

### Principle #3: Review and Test Features in Isolated Environments

ContinuousPipe creates an individual environment for each branch, meaning that features can live in isolated environments until everyone is happy that they are working as intended. This keeps project, integration and production branches clean and reduces the risk of releases being blocked by broken features.

The extent to which this principle is applied can be controlled by branch management - a branch could represent an entire feature or branches could be created for each subtask depending upon requirements.

### Principle #4: Involve Product Owners as Early as Possible

Building on the previous principles, it is much easier to bring in PO approval at the feature branch stage, which can often result in important interventions and corrections to misunderstood requirements. Adapting your workflow to have PO approval as well as QA approval at this stage can therefore save time and money.

In the real world however, POs are usually very busy people, but the benefits are still applicable when using ContinuousPipe to build the integration environment. POs can benefit from the shortened feedback loop to understand when an environment is ready to test and when a change has been made for them to retest.

### Principle #5: Use Remote Development Tooling to Speed Up Development 

ContinuousPipe environments can also be used by developers as a replacement for virtual machines. To achieve this ContinuousPipe provides a utility that syncs their local changes with a remote environment. This allows developers to work faster as they are able to test their local changes on production quality hardware and benefit from up to date environments. They are also able to share a link to their environment with other developers and QA before even raising a PR. This allows problems encountered to be quickly explained and visualised using a browser.
