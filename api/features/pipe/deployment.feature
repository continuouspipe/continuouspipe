Feature:
  In order to easily create or update environments
  As a developer
  I should be able to send a environment configuration and ask Pipe to do what it can to have this environment ready

  Background:
    Given I am authenticated

  @smoke
  Scenario: A valid deployment request
    Given I am building a deployment request
    And the target environment name is "my-environment"
    And the target cluster identifier is "my-cluster"
    And the specification come from the template "simple-app"
    And the credentials bucket is "00000000-0000-0000-0000-000000000000"
    When I send the built deployment request
    Then the deployment request should be successfully created

  Scenario: The credentials bucket is mandatory
    Given I am building a deployment request
    And the target environment name is "my-environment"
    And the target cluster identifier is "my-cluster"
    And the specification come from the template "simple-app"
    When I send the built deployment request
    Then the deployment request should be invalid

  Scenario: The target environment name is mandatory
    Given I am building a deployment request
    And the target cluster identifier is "my-cluster"
    And the specification come from the template "simple-app"
    And the credentials bucket is "00000000-0000-0000-0000-000000000000"
    When I send the built deployment request
    Then the deployment request should be invalid

  Scenario: The target cluster clusterName is mandatory
    Given I am building a deployment request
    And the target environment name is "my-environment"
    And the specification come from the template "simple-app"
    And the credentials bucket is "00000000-0000-0000-0000-000000000000"
    When I send the built deployment request
    Then the deployment request should be invalid

  Scenario: The components specification is mandatory
    Given I am building a deployment request
    And the target environment name is "my-environment"
    And the target cluster identifier is "my-cluster"
    And the credentials bucket is "00000000-0000-0000-0000-000000000000"
    When I send the built deployment request
    Then the deployment request should be invalid
