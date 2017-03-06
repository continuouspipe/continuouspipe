Feature:
  In order to have an understanding of my development environment
  As a user
  I want to get the status of my environment

  Background:
    Given the team "continuous-pipe" exists
    And there is a user "samuel"
    And the user "samuel" is "user" of the team "continuous-pipe"
    And I have a flow with UUID "00000000-0000-0000-0000-000000000000" in the team "continuous-pipe"
    And I am authenticated as "samuel"

  Scenario: Get the status of a newly built development environment
    Given the user "samuel" have a development environment "00000000-0000-0000-0000-000000000000" for the flow "00000000-0000-0000-0000-000000000000"
    When I request the status of the development environment "00000000-0000-0000-0000-000000000000" of the flow "00000000-0000-0000-0000-000000000000"
    Then I should see that the status of the development environment is "TokenNotCreated"

  Scenario: Get the status of a development environment without any running tide
    Given the user "samuel" have a development environment "00000000-0000-0000-0000-000000000000" for the flow "00000000-0000-0000-0000-000000000000"
    And an initialization token have been created for the development environment "00000000-0000-0000-0000-000000000000" of the flow "00000000-0000-0000-0000-000000000000" and the branch "cpdev/sroze"
    When I request the status of the development environment "00000000-0000-0000-0000-000000000000" of the flow "00000000-0000-0000-0000-000000000000"
    Then I should see that the status of the development environment is "NotStarted"

  Scenario: Get the status of a development environment with a running tide
    Given the user "samuel" have a development environment "00000000-0000-0000-0000-000000000000" for the flow "00000000-0000-0000-0000-000000000000"
    And an initialization token have been created for the development environment "00000000-0000-0000-0000-000000000000" of the flow "00000000-0000-0000-0000-000000000000" and the branch "cpdev/sroze"
    And a tide is started for the branch "cpdev/sroze" with a deploy task
    When I request the status of the development environment "00000000-0000-0000-0000-000000000000" of the flow "00000000-0000-0000-0000-000000000000"
    Then I should see that the status of the development environment is "TideRunning"
    And I should see the last tide of my development environment

  Scenario: Get the status of a development environment with a failed tide
    Given the user "samuel" have a development environment "00000000-0000-0000-0000-000000000000" for the flow "00000000-0000-0000-0000-000000000000"
    And an initialization token have been created for the development environment "00000000-0000-0000-0000-000000000000" of the flow "00000000-0000-0000-0000-000000000000" and the branch "cpdev/sroze"
    And a tide is started for the branch "cpdev/sroze" with a deploy task
    And the tide failed
    When I request the status of the development environment "00000000-0000-0000-0000-000000000000" of the flow "00000000-0000-0000-0000-000000000000"
    Then I should see that the status of the development environment is "TideFailed"
    And I should see the last tide of my development environment

  Scenario: Get the status of a development environment with a successful tide
    Given the user "samuel" have a development environment "00000000-0000-0000-0000-000000000000" for the flow "00000000-0000-0000-0000-000000000000"
    And an initialization token have been created for the development environment "00000000-0000-0000-0000-000000000000" of the flow "00000000-0000-0000-0000-000000000000" and the branch "cpdev/sroze"
    And a tide is started for the branch "cpdev/sroze" with a deploy task for the cluster "fake/bar"
    When the deployment succeed with the following public address:
      | name | address |
      | app  | 1.2.3.4  |
    When I request the status of the development environment "00000000-0000-0000-0000-000000000000" of the flow "00000000-0000-0000-0000-000000000000"
    Then I should see that the status of the development environment is "Running"
    And I should see that the cluster identifier of the development environment is "fake/bar"
    And I should see that the public endpoint of the service "app" of my development environment is "1.2.3.4"
    And I should see the environment name of my development environment
    And I should see the last tide of my development environment
