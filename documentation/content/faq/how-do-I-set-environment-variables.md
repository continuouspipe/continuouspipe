---
title: How do I set environment variables?
menu:
  main:
    parent: 'faq'
    weight: 10
---
You may want to change the behaviour of an application based on what environment it is deployed to. This can be done by changing the value of an environment variable passed to a container. For example you may want to set the "SYMFONY_ENV" environment variable to "prod" for production, UAT and feature branch environments. We can do this by creating a variable within `continuous-pipe.yml` and then using it as the value of the environment variable passed to the service.

```
variables:
    - name: SYMFONY_ENVIRONMENT
      value: prod
 
tasks:
    # ...
    deployment:
        deploy:
            services:
                web:
                    specification:
                        # ...
                        environment_variables:
                            - name: SYMFONY_ENV
                              value: ${SYMFONY_ENVIRONMENT}
```

In the "variables" section a "SYMFONY_ENVIRONMENT" variable is created and assigned a value of "prod". This variable then becomes available in the "tasks" section where it is passed to the web service as the environment variable "SYMFONY_ENV" (the local variable could also be called "SYMFONY_ENV" but is different in this example to distinguish between the variable types).

You can use conditions to set different values for the variable:

```
variables:
    - name: SYMFONY_ENVIRONMENT
      value: prod
      condition: code_reference.branch in ["uat", "production"]
    - name: SYMFONY_ENVIRONMENT
      value: dev
      condition: code_reference.branch not in ["uat", "production"]
 
tasks:
    # ...
    deployment:
        deploy:
            services:
                web:
                    specification:
                        # ...
                        environment_variables:
                            - name: SYMFONY_ENV
                              value: ${SYMFONY_ENVIRONMENT}
```
Here "SYMFONY_ENVIRONMENT" is set to "prod" for the uat and production branches only and "dev" for all others.
