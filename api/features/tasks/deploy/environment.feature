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
