---
title: How do I override tasks in pipelines?
menu:
  main:
    parent: 'faq'
    weight: 60
---
You may want to change the way certain tasks are run according to the environment. You could achieve this by defining separate tasks per pipeline, however this runs the risk of introducing a lot of duplication. To avoid excessive duplication ContinuousPipe allows you to override some or all of a task definition.

```
tasks:
    deployment:
        deploy:
            services:
               web:
                   specification:
                       environment_variables:
                           - name: WEB_HTTP
                             value: true
                           - name: WEB_HTTPS
                             value: false
        
pipelines:
    - name: Production
      condition: code_reference.branch == 'master'
      tasks: [ deployment ]
    - name: Uat
      condition: code_reference.branch != 'master'
      tasks: [ deployment ]
```

This configuration sets up a deployment task that disables HTTPS traffic, then defines two pipelines called "Production" and "Uat". 

The deployment task can now be overidden within the pipeline section by "importing" the task: 

```
tasks:
    deployment:
        deploy:
            services:
               web:
                   specification:
                       environment_variables:
                           - name: WEB_HTTP
                             value: true
                           - name: WEB_HTTPS
                             value: false
        
pipelines:
    - name: Production
      condition: code_reference.branch == 'master'
      tasks:
          - imports: deployment
            deploy:
                services:
                    web:
                        specification:
                            environment_variables:
                                - name: WEB_HTTPS
                                  value: true
    - name: Uat
      condition: code_reference.branch != 'master'
      tasks:
          - imports: deployment
            deploy:
                services:
                   web:
                       specification:
                           environment_variables:
                               - name: WEB_HTTP_PORT
                                 value: 8080
```

For the Production pipeline HTTPS traffic is enabled, and for the Uat pipeline HTTPS remains disabled, however the HTTP web port is changed from the default 80 to 8080. 

