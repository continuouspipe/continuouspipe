Feature:
  In order to have a fine control on the deployed services
  As a developer
  I want to be able to skip some components from the deployment

  Scenario: By using the "explicit" way I can skip components
    Given there is 2 application images in the repository
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        - deploy:
              cluster: foo
              services:
                  image0: ~
    """
    When a tide is started
    Then the component "image0" should be deployed
    And the component "image1" should not be deployed

  Scenario: Even if a built service is deployed by a previous task, this can be skipped again
    Given there is 2 application images in the repository
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        build:
            build: ~

        first:
            deploy:
                cluster: foo
                services:
                    image0: ~

        second:
            deploy:
                cluster: foo
                services:
                    image1: ~
    """
    When a tide is started
    And the build task succeed
    Then the component "image0" should be deployed
    And the component "image1" should not be deployed
    And the first deploy succeed
    Then the component "image1" should be deployed
    And the component "image0" should not be deployed
