Feature:
  In order to have feedback about a flows' usage
  As a developer
  I want to view the usage of a flow

  Background:
    Given the team "my-team" exists
    And there is a user "samuel"
    And the user "samuel" is "ADMIN" of the team "my-team"
    And I am authenticated as "samuel"
    And the team "my-team" have the credentials of a cluster "fra-01"
    And I have a flow with UUID "00000000-0000-0000-0000-000000000000" in the team "my-team"

  Scenario: Get usage for flow
    Given I have a deployed environment named "staging" and labelled "flow=00000000-0000-0000-0000-000000000000" on the cluster "fra-01"
    And the environment "staging" on the cluster "fra-01" has component "mysql" with specification:
      """
      {
          "resources": {
              "limits": {
                  "cpu": "250m",
                  "memory": "300Mi"
              },
              "requests": {
                  "cpu": "100m",
                  "memory": "250Mi"
              }
          }
      }
      """
    When I request the usage of the flow "00000000-0000-0000-0000-000000000000"
    Then I should see limits for cpu of "250m" and memory of "300Mi"
    And I should see requests for cpu of "100m" and memory of "250Mi"