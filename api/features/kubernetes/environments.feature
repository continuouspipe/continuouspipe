Feature:
  In order to have an overview of the deployed environments
  As a developer
  I want to have the list and the status of each environment deployed in different namespaces

  Background:
    Given I am authenticated
    And I have a valid Kubernetes provider

  Scenario: I get the list of running components
    Given I have the application "simple-app" deployed
    When I request the environment list of the Kubernetes provider
    Then I should see the component "app"
    And I should see the component "mysql"

  Scenario:
    Given I have the application "simple-app" deployed
    And pods are not running for the replication controller "app"
    When I request the environment list of the Kubernetes provider
    Then the status of the component "app" should be "unhealthy"

  Scenario:
    Given I have the application "simple-app" deployed
    And pods are running but not ready for the replication controller "app"
    When I request the environment list of the Kubernetes provider
    Then the status of the component "app" should be "unhealthy"

  Scenario:
    Given I have the application "simple-app" deployed
    And pods are running for the replication controller "app"
    When I request the environment list of the Kubernetes provider
    Then the status of the component "app" should be "healthy"

  Scenario: The IP of the service should be in the status
    Given I have the application "simple-app" deployed
    And the service "app" have the public IP "1.2.3.4"
    When I request the environment list of the Kubernetes provider
    Then the status of the component "app" should contain the public endpoint "1.2.3.4"

  Scenario: The hostname of the service should be in the status
    Given I have the application "simple-app" deployed
    And the service "app" have the public hostname "foo.bar.dns"
    When I request the environment list of the Kubernetes provider
    Then the status of the component "app" should contain the public endpoint "foo.bar.dns"
