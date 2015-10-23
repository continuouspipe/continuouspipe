Feature:
  In order to keep traces of failed deployment as minimum as possible
  The created components should be deleted if a deployment is failed

  Background:
    Given I am authenticated
    And I have a valid Kubernetes provider

  Scenario: The created component are deleted if the deployment fail
    When I send a deployment request from application template "simple-app"
    And the deployment is failed
    Then the replication controller "mysql" should be deleted
    And the service "mysql" should be deleted

  Scenario: If some components were just updated or simply not created, they shouldn't be deleted
    Given I have an existing replication controller "mysql"
    And I have an existing service "mysql"
    When I send a deployment request from application template "simple-app"
    And  the deployment is failed
    Then the replication controller "app" should be deleted
    And the service "app" should be deleted
    And the replication controller "mysql" should not be deleted
    And the service "mysql" should not be deleted
