Feature:
  In order to have a feedback about the flows' deployments
  As a developer
  I want to be able to see the list of deployed environments of a flow

  Background:
    Given the team "samuel" exists
    And I am authenticated as "samuel"
    And the user "samuel" is "USER" of the team "samuel"
    And the team "samuel" have the credentials of a cluster "fra-01"
    And I have a flow with UUID "00000000-0000-0000-0000-000000000000" in the team "samuel"

  Scenario: Lists the environments that have the flow tag
    Given a tide is created with a deploy task
    And I have a deployed environment named "00000000-0000-0000-0000-000000000000-bar" and labelled "flow=00000000-0000-0000-0000-000000000000" on the cluster "fra-01"
    And I have a deployed environment named "my-custom" and labelled "flow=00000000-0000-0000-0000-000000000000" on the cluster "fra-01"
    When I request the list of deployed environments of the flow "00000000-0000-0000-0000-000000000000"
    Then I should see the environment "00000000-0000-0000-0000-000000000000-bar"
    And I should see the environment "my-custom"

  Scenario: I should not see the environments deployed on a cluster not in the team
    Given a tide is created with a deploy task
    And I have a deployed environment named "an-hidden-one" and labelled "flow=00000000-0000-0000-0000-000000000000" on the cluster "fra-99"
    When I request the list of deployed environments of the flow "00000000-0000-0000-0000-000000000000"
    And I should not see the environment "an-hidden-one"

  Scenario: Only administrators can delete an environment
    Given I have a deployed environment named "staging" and labelled "flow=00000000-0000-0000-0000-000000000000" on the cluster "fra-01"
    When I tentatively delete the environment named "staging" deployed on "fake/foo" of the flow "00000000-0000-0000-0000-000000000000"
    And I should be told that I don't have the permissions

  Scenario: Delete an environment
    Given I am authenticated as "samuel-admin"
    And the user "samuel-admin" is "ADMIN" of the team "samuel"
    And I have a deployed environment named "staging" and labelled "flow=00000000-0000-0000-0000-000000000000" on the cluster "fra-01"
    When I delete the environment named "staging" deployed on "fra-01" of the flow "00000000-0000-0000-0000-000000000000"
    And I request the list of deployed environments of the flow "00000000-0000-0000-0000-000000000000"
    Then I should not see the environment "staging"
    And the environment "staging" should have been deleted

  Scenario: Return empty environment list in case of API error
    Given a tide is created with a deploy task
    And I have a deployed environment named "my-custom" and labelled "flow=00000000-0000-0000-0000-000000000000" on the cluster "fra-01"
    And the environment API calls to the Pipe API failed
    When I request the list of deployed environments of the flow "00000000-0000-0000-0000-000000000000"
    Then I should receive an empty list of environments
    And I should see the "Fetching environment list from Pipe failed." message in the log
