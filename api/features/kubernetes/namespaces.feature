Feature:
  In order to have isolated running environments
  As a developer
  Each environment have to run in isolated namespaces

  Background:
    Given I have a valid Kubernetes provider
    And I am authenticated

  Scenario:
    When I send a deployment request for a non-existing environment
    Then it should create a new namespace

  Scenario:
    Given I have a namespace "foo"
    When I send a deployment request for the environment "foo"
    Then it should reuse this namespace
