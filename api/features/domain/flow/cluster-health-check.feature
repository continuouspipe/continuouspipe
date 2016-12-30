Feature:
  In order to ensure that my deployments are successful
  As an ops
  I want to be able to have insights about my cluster

  Background:
    Given I am authenticated as "sroze"
    And the team "samuel" exists
    And the user "sroze" is "ADMIN" of the team "samuel"

  Scenario: I get alerts for a given cluster
    Given the team "samuel" have the credentials of a cluster "foo" with address "1.2.3.4"
    And the cluster with the address "1.2.3.4" will have the following problems:
      | category            | message                   |
      | schedulable_cpu_low | This is serious my friend |
    When I ask the alerts of the cluster "foo" of the team "samuel"
    Then I should see the following problems:
      | category            | message                   |
      | schedulable_cpu_low | This is serious my friend |
