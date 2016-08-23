Feature:
  In order to have a feedback about the flows' deployments
  As a developer
  I want to be able to see the list of deployed environments of a flow

  Background:
    Given I am authenticated as "samuel"
    And I have a flow with UUID "00000000-0000-0000-0000-000000000000"
    And the user "samuel" is "USER" of the team "samuel"

  Scenario: Lists the environments that have the flow tag
    Given a tide is created with a deploy task
    And I have a deployed environment named "00000000-0000-0000-0000-000000000000-bar" and labelled "flow=00000000-0000-0000-0000-000000000000"
    And I have a deployed environment named "my-custom" and labelled "flow=00000000-0000-0000-0000-000000000000"
    When I request the list of deployed environments of the flow "00000000-0000-0000-0000-000000000000"
    Then I should see the environment "00000000-0000-0000-0000-000000000000-bar"
    And I should see the environment "my-custom"

  Scenario: Only administrators can delete an environment
    Given I have a deployed environment named "staging" and labelled "flow=00000000-0000-0000-0000-000000000000"
    When I tentatively delete the environment named "staging" deployed on "fake/foo" of the flow "00000000-0000-0000-0000-000000000000"
    And I should be told that I don't have the permissions

  Scenario: Delete an environment
    Given I am authenticated as "samuel-admin"
    And the user "samuel-admin" is "ADMIN" of the team "samuel"
    And I have a deployed environment named "staging" and labelled "flow=00000000-0000-0000-0000-000000000000"
    When I delete the environment named "staging" deployed on "fake/foo" of the flow "00000000-0000-0000-0000-000000000000"
    And I request the list of deployed environments of the flow "00000000-0000-0000-0000-000000000000"
    Then I should not see the environment "staging"
    And the environment "staging" should have been deleted
