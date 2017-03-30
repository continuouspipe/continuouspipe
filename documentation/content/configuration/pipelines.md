---
title: Pipelines
menu:
  main:
    parent: 'configuration'
    weight: 70

weight: 70
---
You can use a `pipelines` section to simplify your configuration. By default tasks run in the order they are defined, but the addition of pipeline configuration disables this behaviour, and instead the pipelines become responsible for defining the task sequence.
 
In the following example two pipelines are created, and used to control which tasks are run.

``` yaml
tasks:
    images:
        # ...
    migrations:
        # ...
    deployment:
        # ...
        
pipelines:
    - name: Production
      condition: 'code_reference.branch in ["uat", "production"]'
      tasks: [ images, deployment ]
    - name: Staging
      condition: '"Ready for review" in pull_request.labels'
      tasks: [ images, migrations, deployment ]
```

Branches with "uat" or "production" in their name will be routed to the `Production` pipeline, where the `images` and `deployment` tasks will be run during the build. Branches that have the label "Ready for review" will be routed to the "Staging" pipeline, where the `images`, `migrations` and `deployment` tasks will be run during the build.

## Overriding Variables

Pipelines can be used to override variables:

``` yaml
variables:
    - name: SYMFONY_ENV
      value: prod

tasks:
    images:
        # ...
    migrations:
        # ...
    deployment:
        # ...
        
pipelines:
    - name: Production
      condition: 'code_reference.branch in ["uat", "production"]'
      tasks: [ images, deployment ]
    - name: Staging
      condition: '"Ready for review" in pull_request.labels'
      tasks: [ images, migrations, deployment ]
      variables:
          - name: SYMFONY_ENV
            value: dev
```

Here the `SYMFONY_ENV` variable is set to "prod", but is overridden within the "Staging" pipeline and set to "dev".

## Overriding Tasks

Pipelines can also be used to override tasks.

In the following configuration a `deployment` task has been defined, which disables HTTPS traffic. There are also two pipelines which run the task.

``` yaml
tasks:
    images:
        # ...
    migrations:
        # ...
    deployment:
        deploy:
            services:
               web:
                   specification:
                       environment_variables:
                           - name: WEB_HTTPS
                             value: false
        
pipelines:
    - name: Production
      condition: 'code_reference.branch in ["uat", "production"]'
      tasks: [ images, deployment ]
    - name: Staging
      condition: '"Ready for review" in pull_request.labels'
      tasks: [ images, migrations, deployment ]
```

To override the task within a pipeline, you can import it using `imports`, then make any changes you need to. In the below example the `Production` pipeline overrides `environment_variables` within the `deployment` task to enable HTTPS traffic:

``` yaml
tasks:
    images:
        # ...
    migrations:
        # ...
    deployment:
        deploy:
            services:
               web:
                   specification:
                       environment_variables:
                           - name: WEB_HTTPS
                             value: false
        
pipelines:
    - name: Production
      condition: 'code_reference.branch in ["uat", "production"]'
      tasks:
        - images
        - imports: deployment
          deploy:
              services:
                  web:
                      specification:
                          environment_variables:
                              - name: WEB_HTTPS
                                value: true
    - name: Staging
      condition: '"Ready for review" in pull_request.labels'
      tasks: [ images, migrations, deployment ]
```