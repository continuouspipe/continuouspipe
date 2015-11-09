Feature:
  In order to keep traces of failed deployment as minimum as possible
  The created components should be deleted if a deployment is failed

  Background:
    Given I am authenticated
    And there is a cluster in the bucket "00000000-0000-0000-0000-000000000000" with the following configuration:
      | identifier | type       | address         | version | username | password |
      | my-cluster | kubernetes | https://1.2.3.4 | v1      | username | password |
    And I am building a deployment request
    And the target environment name is "my-environment"
    And the target cluster identifier is "my-cluster"
    And the credentials bucket is "00000000-0000-0000-0000-000000000000"

  Scenario: The created component are deleted if the deployment fail
    Given the specification come from the template "simple-app"
    When I send the built deployment request
    And the deployment is failed
    Then the replication controller "mysql" should be deleted
    And the service "mysql" should be deleted

  Scenario: If some components were just updated or simply not created, they shouldn't be deleted
    Given I have an existing replication controller "mysql"
    And I have an existing service "mysql"
    Given the specification come from the template "simple-app"
    When I send the built deployment request
    And the deployment is failed
    Then the replication controller "app" should be deleted
    And the service "app" should be deleted
    And the replication controller "mysql" should not be deleted
    And the service "mysql" should not be deleted
