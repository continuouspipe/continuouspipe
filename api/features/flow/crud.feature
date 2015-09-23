Feature:
  In order to setup continuous delivery on a new project
  As a developer
  I need to be able to create a new flow

  Background:
    Given I am authenticated

  Scenario:
    When I send a flow creation request
    Then the flow is successfully saved

  @smoke
  Scenario:
    Given I have a flow
    When I send an update request with a configuration
    Then the flow is successfully saved
    And the stored configuration is not empty
