Feature:
  In order to prevent updating some components of the application that rely on datas
  As a devops guy
  I want to be able to lock component update

  Background:
    Given I am authenticated
    And I have a valid Kubernetes provider

  Scenario:
    When I send a deployment request from application template "simple-app"
    Then the replication controller "mysql" should be created

  Scenario:
    Given I have an existing replication controller "mysql"
    And I have an existing replication controller "app"
    When I send a deployment request from application template "simple-app"
    Then the replication controller "mysql" shouldn't be updated
