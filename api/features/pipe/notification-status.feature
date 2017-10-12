Feature:
  In order to have actions based on the created/updated/deleted components
  As a developer
  I want to receive a deployment status per component to know what happened

  Background:
    Given I am authenticated
    And there is a cluster in the bucket "00000000-0000-0000-0000-000000000000" with the following configuration:
      | identifier | type       | address         | version | username | password |
      | my-cluster | kubernetes | https://1.2.3.4 | v1      | username | password |
    And I am building a deployment request
    And the target environment name is "my-environment"
    And the target cluster identifier is "my-cluster"
    And the credentials bucket is "00000000-0000-0000-0000-000000000000"
    And the specification come from the template "simple-app"
    And the notification callback address is "http://example.com"
    And the pods of the replication controllers will be created successfully and running

  @smoke
  Scenario: The status of the deployed components should be sent in the notification
    When I send the built deployment request
    Then one notification should be sent back
    And the notification should contain the status of the component "app"
    And the notification should contain the status of the component "mysql"
    And the deployment status "created" of the component "app" should be true
    And the deployment status "created" of the component "mysql" should be true
    And the deployment status "updated" of the component "mysql" should be false

  Scenario: The status of the deployed components should be sent in the notification
    Given I have an existing replication controller "mysql"
    And I have an existing service "mysql"
    When I send the built deployment request
    Then one notification should be sent back
    And the notification should contain the status of the component "app"
    And the notification should contain the status of the component "mysql"
    And the deployment status "created" of the component "mysql" should be false
    And the deployment status "updated" of the component "mysql" should be false
