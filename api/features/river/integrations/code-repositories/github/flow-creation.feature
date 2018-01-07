Feature:
  In order to start deployments
  As a user
  I want to be able to create a flow from a BitBucket repository

  Background:
    Given I am authenticated as "samuel"

  Scenario: Create from a GitHub repositroy
    Given the GitHub repository "1234" exists
    And the team "foo" exists
    When I send a flow creation request for the team "foo" with the GitHub repository "1234"
    Then the flow is successfully saved

  Scenario: I force the UUID of a flow
    Given the GitHub repository "1234" exists
    And the team "foo" exists
    When I send a flow creation request for the team "foo" with the GitHub repository "1234" and the UUID "00000000-0000-0000-0000-000000000000"
    Then the flow is successfully saved
    And the flow UUID should be "00000000-0000-0000-0000-000000000000"
