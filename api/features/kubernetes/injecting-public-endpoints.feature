Feature:
  In order to communicate between different public components of an application
  As a starting container
  I need to know my own public endpoint

  Background:
    Given I am authenticated
    And I am building a deployment request
    And the target environment name is "my-environment"
    And the target cluster identifier is "my-cluster"
    And the credentials bucket is "00000000-0000-0000-0000-000000000000"
    And there is a cluster in the bucket "00000000-0000-0000-0000-000000000000" with the following configuration:
      | identifier | type       | address         | version | username | password |
      | my-cluster | kubernetes | https://1.2.3.4 | v1      | username | password |
    And the pods of the replication controllers will be created successfully and running

  @smoke
  Scenario:
    Given the service "api" will be created with the public IP "1.2.3.4"
    And the service "ui" will be created with the public IP "5.6.7.8"
    When the specification come from the template "two-public-components"
    And I send the built deployment request
    Then the replication controller "api" should be created with the following environment variables:
      | name                        | value   |
      | SERVICE_API_PUBLIC_ENDPOINT | 1.2.3.4 |
      | SERVICE_UI_PUBLIC_ENDPOINT  | 5.6.7.8 |
    And the replication controller "ui" should be created with the following environment variables:
      | name    | value   |
      | API_URL | 1.2.3.4 |

  Scenario: The public endpoint should be the HTTPLabs ones if the components are proxied
    Given the service "api" will be created with the public IP "1.2.3.4"
    And the service "ui" will be created with the public IP "5.6.7.8"
    When the specification come from the template "two-proxied-components"
    And I send the built deployment request
    Then the replication controller "api" should be created with the following environment variables:
      | name                        | value                          |
      | SERVICE_API_PUBLIC_ENDPOINT | badger-carrot-5678.httplabs.io |
      | SERVICE_UI_PUBLIC_ENDPOINT  | monkey-potato-5678.httplabs.io |

  Scenario: The public endpoint should be the DNS addresses if the load balancer have DNS addresses
    Given the service "api" will be created with the public DNS address "api.my-custom-dns"
    And the service "ui" will be created with the public DNS address "1234.foo.ui.docker"
    When the specification come from the template "two-public-components"
    And I send the built deployment request
    Then the replication controller "api" should be created with the following environment variables:
      | name                        | value              |
      | SERVICE_API_PUBLIC_ENDPOINT | api.my-custom-dns  |
      | SERVICE_UI_PUBLIC_ENDPOINT  | 1234.foo.ui.docker |

  Scenario: The public services should not be updated if the selector are the same
    Given I have a service "app" with the selector "com.continuouspipe.visibility=public,component-identifier=app"
    And the service "app" will be created with the public IP "1.2.3.4"
    When the specification come from the template "simple-app-public"
    And I send the built deployment request
    Then the service "app" should not be updated
    And the service "app" should not be deleted
    And the service "app" should not be created

  Scenario: The public services should be deleted and then created if selectors are different
    Given I have a service "app" with the selector "component-identifier=app"
    And the service "app" will be created with the public IP "1.2.3.4"
    When the specification come from the template "simple-app-public"
    And I send the built deployment request
    Then the service "app" should not be updated
    And the service "app" should be deleted
    And the service "app" should be created
