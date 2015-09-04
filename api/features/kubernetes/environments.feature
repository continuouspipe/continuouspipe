Feature:
  In order to have an overview of the deployed environments
  As a developer
  I want to have the list and the status of each environment deployed in different namespaces

  Background:
    Given I am authenticated
    And I have a valid Kubernetes provider

  Scenario:
    Given I have the application "simple-app-public" deployed
    When I request the environment list of the Kubernetes provider
    Then I should see the component "api" in environment "simple-app-public"
    And I should see the component "mysql" in environment "simple-app-public"
