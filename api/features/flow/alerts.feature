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
    Then I should see the "github-integration" alert

  Scenario: A team without any cluster should have an alert
    When I load the alerts of the flow "00000000-0000-0000-0000-000000000000"
    Then I should see the "team-missing-cluster" alert

  Scenario: A team with cluster should not have an alert
    Given the team "samuel" have the credentials of a cluster "foo"
    When I load the alerts of the flow "00000000-0000-0000-0000-000000000000"
    Then I should not see the "team-missing-cluster" alert

  Scenario: A team without any docker registry should have an alert
    When I load the alerts of the flow "00000000-0000-0000-0000-000000000000"
    Then I should see the "team-missing-docker-registry" alert

  Scenario: A team with a docker registry should not have an alert
    Given the team "samuel" have the credentials of a Docker registry "gcr.io"
    When I load the alerts of the flow "00000000-0000-0000-0000-000000000000"
    Then I should not see the "team-missing-docker-registry" alert

  Scenario: A missing variable will produce an alert
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        kubernetes:
            deploy:
                cluster: ${CLUSTER}
                services: []
    """
    When I load the alerts of the flow "00000000-0000-0000-0000-000000000000"
    Then I should see the "missing-variable" alert
