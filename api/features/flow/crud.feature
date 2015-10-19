Feature:
  In order to setup continuous delivery on a new project
  As a developer
  I need to be able to create a new flow

  Background:
    Given I am authenticated

  Scenario: I can list the flows of a team
    Given the team "samuel" exists
    And I have a flow with UUID "00000000-0000-0000-0000-000000000000" in the team "samuel"
    When I retrieve the list of the flows of the team "samuel"
    Then I should see the flow "00000000-0000-0000-0000-000000000000"

  Scenario: I can see the last tide of the flow
    Given the team "samuel" exists
    And I have a flow in the team "samuel"
    And a tide is created
    When I retrieve the list of the flows of the team "samuel"
    Then I should see the flow's last tide

  Scenario: If the team name is missing, the request should be failed
    Given the GitHub repository "1234" exists
    When I send a flow creation request with the following parameters:
    | repository |
    | 1234       |
    Then I should be told that my flow creation request is invalid

  Scenario: If the creation request is valid, the flow should be created
    Given the GitHub repository "1234" exists
    And the team "foo" exists
    When I send a flow creation request with the following parameters:
    | repository | team |
    | 1234       | foo  |
    Then the flow is successfully saved

  Scenario: I can force the UUID of a flow
    Given the GitHub repository "1234" exists
    And the team "foo" exists
    When I send a flow creation request with the following parameters:
      | repository | team | uuid                                 |
      | 1234       | foo  | 00000000-0000-0000-0000-000000000000 |
    Then the flow is successfully saved
    And the flow UUID should be "00000000-0000-0000-0000-000000000000"

  @smoke
  Scenario:
    Given I have a flow
    When I send an update request with a configuration
    Then the flow is successfully saved
    And the stored configuration is not empty
