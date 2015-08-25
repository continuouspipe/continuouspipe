Feature:
  In order to communicate between different public components of an application
  As a starting container
  I need to know my own public endpoint

  Background:
    Given I am authenticated
    And I have a valid Kubernetes provider

  @wip
  Scenario:
    When I send a deployment request from application template "two-public-components"
    And the service "api" is created with the public endpoint "1.2.3.4"
    And the service "ui" is created with the public endpoint "5.6.7.8"
    Then the replication controller "api" should be created with the following environment variables:
    | variable                    | value   |
    | SERVICE_API_PUBLIC_ENDPOINT | 1.2.3.4 |
    | SERVICE_UI_PUBLIC_ENDPOINT  | 5.6.7.8 |
