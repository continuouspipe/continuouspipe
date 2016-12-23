Feature:
  In order to run deployments
  As a BitBucket user
  I want to be able to start tides

  Background:
    Given I am authenticated as "samuel"
    And the team "samuel" exists
    And the user "samuel" is "USER" of the team "samuel"

  Scenario: Create a tide from the code repository's configuration
    Given I have a flow with a BitBucket repository
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        images:
            build: {services: []}
    """
    When I send a tide creation request for branch "master"
    Then the tide should be created
    And the tide should have the task "images"
