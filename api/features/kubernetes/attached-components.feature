Feature:
  In order to run a container or a command as part of the deployment
  As a developer
  I want to be able to deploy services in an attached mode

  Background:
    Given I am authenticated
    And I have a valid Kubernetes provider

  Scenario: It creates only the pod as the component is not scalable
    Given the pod "app" will run successfully
    When I send a deployment request from application template "attached-component"
    Then the pod "app" should be created
    And the service "app" should not be created
    And the replication controller "app" should not be created

  Scenario: It will fail the deployment if the pod exit with a status code different than 0
    Given the pod "app" will fail with exit code 1
    When I send a deployment request from application template "attached-component"
    And the deployment should be failed
    And the pod "app" should be deleted

  Scenario: If pod is successful then the deployment too
    Given the pod "app" will run successfully
    When I send a deployment request from application template "attached-component"
    And the deployment should be successful
    And the pod "app" should be deleted
