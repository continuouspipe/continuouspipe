Feature:
  In order to have a deployment flexibility
  As a developer
  I want to be able to configure the deployed services and this services successfully transformed to pipe components

  Scenario: I can manually create services
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        - build:
              services:
                  two:
                      image: mine
        - deploy:
              cluster: foo
              services:
                  one:
                      specification:
                          source:
                              image: mysql
                      locked: true
                  two:
                      specification:
                          accessibility:
                              from_external: true
                          ports:
                              - identifier: twohttp
                                port: 80
    """
    When a tide is started
    And the build succeed
    Then the component "one" should be deployed as locked
    And the component "one" should not be deployed as accessible from outside
    And the component "two" should be deployed as accessible from outside
    And the component "two" should be deployed with the image "mine"
    And the component "two" should be deployed with a TCP port 80 named "twohttp" opened

  Scenario: I can use persistent volumes
    Given there is 1 application images in the repository
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        deployment:
            deploy:
                cluster: foo
                services:
                    image0:
                        specification:
                            volumes:
                                - type: persistent
                                  name: api-volume
                                  capacity: 5Gi
                            volume_mounts:
                                - name: api-volume
                                  mount_path: /var/lib/app
    """
    When a tide is started
    Then the component "image0" should be deployed
    And the component "image0" should have a persistent volume mounted at "/var/lib/app"

  Scenario: I can use the reverse-proxy extension
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        - deploy:
              cluster: foo
              services:
                  one:
                      specification:
                          source:
                              image: mysql
                          accessibility:
                              from_external: true
                          ports:
                              - identifier: twohttp
                                port: 80
                      extensions:
                          reverse_proxy:
                              domain_names:
                                  - example.com
    """
    When a tide is started
    Then the component "one" should be deployed
    And the component "one" should be deployed with the reverse proxy extension and contains the domain name "example.com"

  Scenario: Fills the guessed port for all the deploy tasks
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        first:
            deploy:
                cluster: foo
                services:
                    app:
                        specification:
                            source:
                                image: my/app
                            accessibility:
                                from_external: true
                            ports:
                                - identifier: web1
                                  port: 80
        second:
            deploy:
                cluster: foo
                services:
                    app:
                        specification:
                            source:
                                image: my/app
                            accessibility:
                                from_external: true
                            ports:
                                - identifier: web2
                                  port: 80
    """
    When a tide is started
    Then the component "app" should be deployed with a TCP port 80 named "web1" opened
    And the first deploy succeed
    Then the component "app" should be deployed with a TCP port 80 named "web2" opened

  Scenario: Can specify only the port number
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        first:
            deploy:
                cluster: foo
                services:
                    app:
                        specification:
                            source:
                                image: my/app
                            accessibility:
                                from_external: true
                            ports:
                                - 80
    """
    When a tide is started
    Then the component "app" should be deployed with a TCP port 80

  Scenario: The environment is created with some tags
    Given I have a flow with UUID "00000000-0000-0000-0000-000000000000"
    When I tide is started with the following configuration:
    """
    tasks:
        first:
            deploy:
                cluster: foo
                services:
                    app:
                        specification:
                            source:
                                image: my/app
                            accessibility:
                                from_external: true
                            ports:
                                - 80
    """
    Then the deployed environment should have the tag "flow=00000000-0000-0000-0000-000000000000"

  Scenario: By default, do not precise the number of replicas in the deployment request
    Given there is 1 application images in the repository
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        deployment:
            deploy:
                cluster: foo
                services:
                    image0:
                        specification:
                            scalability:
                                enabled: true
    """
    When a tide is started
    Then the component "image0" should be deployed
    And the component "image0" should be deployed as scaling
    And the component "image0" should be deployed with an unknown number of replicas

  Scenario: Explicit number of replicas
    Given there is 1 application images in the repository
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        deployment:
            deploy:
                cluster: foo
                services:
                    image0:
                        specification:
                            scalability:
                                enabled: true
                                number_of_replicas: 5
    """
    When a tide is started
    Then the component "image0" should be deployed
    And the component "image0" should be deployed as scaling
    And the component "image0" should be deployed with 5 replicas

  Scenario: Probes
    Given there is 1 application images in the repository
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        deployment:
            deploy:
                cluster: foo
                services:
                    image0:
                        deployment_strategy:
                            readiness_probe:
                                initial_delay_seconds: 5
                                timeout_seconds: 5
                                period_seconds: 5
                                type: http
                                path: /
    """
    When a tide is started
    Then the component "image0" should be deployed
    And the readiness probe of the component "image0" should be an http probe on path "/"
