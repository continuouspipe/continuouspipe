# River

```
Image (1)-> Component       ------------+
  |             |                       |
  |             v                       |
  |          Application                |
  |                ^                    |
  |                |                    v
  Tag   (2)->  Environment  (3)->  DeployComponentCommand
                   |
                   v
                Exists ?
                   |
                   +--> CreateEnvironmentCommand
```

*What to deploy ?*
1. Which component(s) ?
2. On which environment ?

*Where to deploy ?*
1. Configuration link between image and application
2. Automated and/or explicit rules between tag and environment

*How to deploy ?*
1. Which adapter and adapter configuration
