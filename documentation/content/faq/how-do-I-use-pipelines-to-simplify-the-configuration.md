---
title: How do I use "pipelines" to simplify the configuration?
menu:
  main:
    parent: 'faq'
    weight: 40
---
You can use variable conditions and task filters to change behaviour based on which branch/environment is being deployed. This can lead to the conditions/filters being repeated in lots of places within the continuous-pipe.yml file. This can be cleaned up using pipelines.

```
variables:
    # ...
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
    - name: Features
      condition: '"Ready for review" in pull_request.labels'
    - name: Remote
      condition: 'code_reference.branch matches "/^cpdev/"'
```

This sets up three pipelines, one for the production and uat branches, one for branches with an open pull request with the "Ready for review" label and one for remote development branches.

You can now specify which tasks to run for these pipelines:

```
variables:
    # ...
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
    - name: Features
      condition: '"Ready for review" in pull_request.labels'
      tasks: [ images, migrations, deployment ]
    - name: Remote
      condition: 'code_reference.branch matches "/^cpdev/"'
      tasks: [ images, migrations, deployment ]
```

Here migrations would not be run automatically on production and uat. If a commit is pushed that doesn't match any of these pipelines then no tasks will be run.

You can also specify the values of variables using pipelines:

```
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
    - name: Features
      condition: '"Ready for review" in pull_request.labels'
      tasks: [ images, migrations, deployment ]
    - name: Remote
      condition: 'code_reference.branch matches "/^cpdev/"'
      tasks: [ images, migrations, deployment ]
      variables:
          - name: SYMFONY_ENV
            value: dev
```

Here the "SYMFONY_ENV" variable is set to "prod" in the standard variables section but has its value overridden to "dev" just for the remote pipeline.
