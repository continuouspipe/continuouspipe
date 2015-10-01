Feature:
  In order to setup continuous delivery on a new project
  As a developer
  I need to be able to create a new flow

  Background:
    Given I am authenticated

  Scenario:
    When I send a flow creation request
    Then the flow is successfully saved

  Scenario: I can force the UUID of a flow
    When I send a flow creation request with the UUID "00000000-0000-0000-0000-000000000000"
    Then the flow is successfully saved
    And the flow UUID should be "00000000-0000-0000-0000-000000000000"

  @smoke
  Scenario:
    Given I have a flow
    When I send an update request with a configuration
    Then the flow is successfully saved
    And the stored configuration is not empty
