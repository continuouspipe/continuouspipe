Feature:
  In order to have a deployment flexibility
  As a developer
  I want to be able to configure the deployed services and this services successfully transformed to pipe components

  Scenario:
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        - build:
              services:
                  two:
                      image: mine
        - deploy:
              providerName: foo
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
