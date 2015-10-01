Feature:
  In order to orchestrates tides for different scenarios
  As a developer
  I want to be able to run manually a tide

  Background:
    Given I have a flow with the following configuration:
    """
    tasks:
        - build:
              services: []
    """
    And I am authenticated

  Scenario: I can create a tide with a branch name and commit SHA1
    When I send a tide creation request for branch "foo" and commit "1234"
    Then a tide should be created

  Scenario: I can create a tide with a branch name if the branch name is resolvable
    And the head commit of branch "foo" is "4321"
    When I send a tide creation request for branch "foo"
    Then a tide should be created

  Scenario: An error should be send back if the branch do not exists
    When I send a tide creation request for branch "foo"
    Then a bad request error should be returned

  Scenario: An error should be sent back if the request do not contain the branch name
    When I send a tide creation request for commit "1234"
    Then a bad request error should be returned
