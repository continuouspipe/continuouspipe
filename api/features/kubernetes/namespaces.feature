Feature:
  In order to have isolated running environments
  As a developer
  Each environment have to run in isolated namespaces

  Background:
    Given I have a valid Kubernetes provider
    And I am authenticated

  @smoke
  Scenario:
    When I send a deployment request for a non-existing environment
    Then it should create a new namespace
    And it should dispatch the namespace created event

  Scenario:
    Given I have a namespace "foo"
    When I send a deployment request for the environment "foo"
    Then it should reuse this namespace

  Scenario:
    When a namespace is created
    Then the secret "continuousPipeDockerRegistries" should be created
    And the service account should be updated with a pull secret "continuousPipeDockerRegistries"
