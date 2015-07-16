Feature:
  In order to have to run real integration tests
  As developer
  I should be able to have a running environment for a pull request

  Scenario: A running environment of my application should be created when I create a pull-request
    When I create a pull request
    Then the branch should be tracked
    And the images should be built
    And the matching environment should be deployed

  Scenario: The environment of my pull-request should be updated when a add commit
    Given I have a tracked branch
    When I push a new commit on that branch
    Then the images should be built
    And the environment should be updated
