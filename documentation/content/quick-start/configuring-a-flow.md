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

We are going to variablise the image name and the cluster identifier that were defined in `continuous-pipe.yml` when [configuring your repository]({{< relref "configuring-your-repository.md" >}}). Here's a reminder of what that looks like:

```
tasks:
    images:
        build:
            services:
                web:
                    image: docker.io/pswaine/hello-world

    deployment:
        deploy:
            cluster: hello-world
            services:
                web:
                    specification:
                        accessibility:
                            from_external: true
```

To create the image variable click "ADD A VARIABLE" and then enter:

- **Name**: "IMAGE_NAME"
- **Value**: "docker.io/pswaine/hello-world"

To create the cluster variable click "ADD A VARIABLE" and then enter:

- **Name**: "CLUSTER"
- **Value**: "hello-world"

Then click "SAVE".

![](/images/quick-start/flow-configuration-overview.png)

The cluster note in `continuous-pipe.yml` can now be updated:

```
tasks:
    images:
        build:
            services:
                web:
                    image: ${IMAGE_NAME}

    deployment:
        deploy:
            cluster: ${CLUSTER}
            services:
                web:
                    specification:
                        accessibility:
                            from_external: true
```

Once this is committed, the variables in the Flow configuration will now be used instead of the hard coded value set previously.