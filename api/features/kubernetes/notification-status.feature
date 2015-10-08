Feature:
  In order to have actions based on the created/updated/deleted components
  As a developer
  I want to receive a deployment status per component to know what happened

  Background:
    Given I am authenticated
    And I have a valid Kubernetes provider

  @smoke
  Scenario: The status of the deployed components should be sent in the notification
    When I send a deployment request from application template "simple-app"
    Then one notification should be sent back
    And the notification should contain the status of the component "app"
    And the notification should contain the status of the component "mysql"
    And the deployment status "created" of the component "app" should be true
    And the deployment status "created" of the component "mysql" should be true
    And the deployment status "updated" of the component "mysql" should be false

  Scenario: The status of the deployed components should be sent in the notification
    Given I have an existing replication controller "mysql"
    And I have an existing service "mysql"
    When I send a deployment request from application template "simple-app"
    Then one notification should be sent back
    And the notification should contain the status of the component "app"
    And the notification should contain the status of the component "mysql"
    And the deployment status "created" of the component "mysql" should be false
    And the deployment status "updated" of the component "mysql" should be false
