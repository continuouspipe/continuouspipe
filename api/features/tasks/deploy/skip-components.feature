Feature:
  In order to have a fine control on the deployed services
  As a developer
  I want to be able to skip some components from the deployment

  Scenario:
    Given there is 2 application images in the repository
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        - deploy:
              providerName: foo
              services:
                  image1: false
    """
    When a tide is started
    Then the component "image0" should be deployed
    And the component "image1" should not be deployed
