Feature:
  In order to communicate between different public components of an application
  As a starting container
  I need to know my own public endpoint

  Background:
    Given I am authenticated
    And I have a valid Kubernetes provider

  @smoke
  Scenario:
    Given the service "api" will be created with the public IP "1.2.3.4"
    And the service "ui" will be created with the public IP "5.6.7.8"
    When I send a deployment request from application template "two-public-components"
    Then the replication controller "api" should be created with the following environment variables:
      | name                        | value   |
      | SERVICE_API_PUBLIC_ENDPOINT | 1.2.3.4 |
      | SERVICE_UI_PUBLIC_ENDPOINT  | 5.6.7.8 |
    And the replication controller "ui" should be created with the following environment variables:
      | name    | value   |
      | API_URL | 1.2.3.4 |

  Scenario:
    Given the service "api" will be created with the public IP "1.2.3.4"
    And the service "ui" will be created with the public IP "5.6.7.8"
    When I send a deployment request from application template "two-proxied-components"
    Then the replication controller "api" should be created with the following environment variables:
      | name                        | value                          |
      | SERVICE_API_PUBLIC_ENDPOINT | badger-carrot-5678.httplabs.io |
      | SERVICE_UI_PUBLIC_ENDPOINT  | monkey-potato-5678.httplabs.io |

  Scenario: The public services should not be updated if the selector are the same
    Given I have a service "app" with the selector "com.continuouspipe.visibility=public,component-identifier=app"
    And the service "app" will be created with the public IP "1.2.3.4"
    When I send a deployment request from application template "simple-app-public"
    Then the service "app" should not be updated
    And the service "app" should not be deleted
    And the service "app" should not be created

  Scenario: The public services should be deleted and then created if selectors are different
    Given I have a service "app" with the selector "component-identifier=app"
    And the service "app" will be created with the public IP "1.2.3.4"
    When I send a deployment request from application template "simple-app-public"
    Then the service "app" should not be updated
    And the service "app" should be deleted
    And the service "app" should be created
