---
title: Configuring a Flow
menu:
  main:
    parent: 'quick-start'
    weight: 60

weight: 60
---
The final step before executing the Flow is to configure it. You can do this by clicking on the Flow, then selecting the "Configuration" tab. This will present you with an interface that allows you to define YAML variables.
 
![](/images/quick-start/flow-configuration-no-config.png)

We are going to variablise the cluster identifier. The cluster identifier is defined when [configuring a cluster]({{< relref "configuring-a-cluster.md" >}}) then referenced in the `continuous-pipe.yml` when [configuring your repository]({{< relref "configuring-your-repository.md" >}}).

To create the variable click "ADD A VARIABLE" and then enter:

- **Name**: "CLUSTER"
- **Value**: "acme-products"

Then click "SAVE".

![](/images/quick-start/flow-configuration-overview.png)

The cluster note in `continuous-pipe.yml` can now be updated:

```
tasks:
    deployment:
        deploy:
            cluster: ${CLUSTER}
            services:
                web:
                    specification:
                        accessibility:
                            from_external: true
```

This will now reference the variable in the Flow configuration value instead of the hard coded value used previously.