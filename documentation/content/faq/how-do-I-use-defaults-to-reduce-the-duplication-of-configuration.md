---
title: How do I use "defaults" to reduce the duplication of configuration?
menu:
  main:
    parent: 'faq'
    weight: 30
---
In the following example the definition of the "cluster" and environment "name" variables are duplicated across tasks:

```
tasks:
    initialise:
        run:
            cluster: ${CLUSTER}
            environment:
                name:  '"sfdemo-" ~ code_reference.branch'
            # ...
   deployments:
        deploy:
            cluster: ${CLUSTER}
            environment:
                name:  '"sfdemo-" ~ code_reference.branch'
            # ...
```

You can avoid this duplication by defining them outside the "tasks" section in a separate "defaults" section:

```
defaults:
     cluster: ${CLUSTER}
     environment:
        name:  '"sfdemo-" ~ code_reference.branch'
  
  
tasks:
    initialise:
        run:
            # ...
    deployments:
        deploy:
            # ...
```

The cluster and environment name will now be set for both the initialise and deployments tasks within the "tasks" section.
