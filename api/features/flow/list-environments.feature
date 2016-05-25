Feature:
  In order to have a feedback about the flows' deployments
  As a developer
  I want to be able to see the list of deployed environments of a flow

  Background:
    Given I am authenticated
    And I have a flow with UUID "00000000-0000-0000-0000-000000000000"

  Scenario: List environments starting with the environment's UUID
    Given a tide is created with a deploy task
    And I have a deployed environment named "00000000-0000-0000-0000-000000000000-master"
    And I have a deployed environment named "11111111-1111-1111-1111-111111111111-bar"
    When I request the list of deployed environments of the flow "00000000-0000-0000-0000-000000000000"
    Then I should see the environment "00000000-0000-0000-0000-000000000000-master"
    Then I should not see the environment "11111111-1111-1111-1111-111111111111-bar"

  Scenario: I receive an empty list when there's no deploy task on the flow
    And a tide is created with just a build task
    When I request the list of deployed environments of the flow "00000000-0000-0000-0000-000000000000"
    Then I should receive an empty list of environments

  Scenario: Lists the environments that have the flow tag
    Given a tide is created with a deploy task
    And I have a deployed environment named "00000000-0000-0000-0000-000000000000-bar" and labelled "flow=00000000-0000-0000-0000-000000000000"
    And I have a deployed environment named "my-custom" and labelled "flow=00000000-0000-0000-0000-000000000000"
    When I request the list of deployed environments of the flow "00000000-0000-0000-0000-000000000000"
    Then I should see the environment "00000000-0000-0000-0000-000000000000-bar"
    And I should see the environment "my-custom"

  Scenario: Delete an environment
    Given I have a deployed environment named "staging" and labelled "flow=00000000-0000-0000-0000-000000000000"
    When I delete the environment named "staging" deployed on "fake/foo" of the flow "00000000-0000-0000-0000-000000000000"
    And I request the list of deployed environments of the flow "00000000-0000-0000-0000-000000000000"
    Then I should not see the environment "staging"
    And the environment "staging" should have been deleted
