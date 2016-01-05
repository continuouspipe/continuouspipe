Feature:
  In order to control the scalability of my deployed application
  As a developer
  I want to be able to ensure the number of replicas can be either static or dynamic

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
