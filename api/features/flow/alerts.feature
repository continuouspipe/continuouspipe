Feature:
  In order to reduce the unexpected errors
  As a user
  I want to be alerted in case of misconfiguration

  Background:
    Given I am authenticated as "samuel"
    And the team "samuel" exists
    And the user "samuel" is "user" of the team "samuel"
    And I have a flow with UUID "00000000-0000-0000-0000-000000000000" in the team "samuel"

  Scenario: The GitHub integration is not found
    When I load the alerts of the flow "00000000-0000-0000-0000-000000000000"
    Then I should see the "github-integration-not-found" alert
