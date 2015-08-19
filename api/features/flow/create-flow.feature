Feature:
  In order to setup continuous delivery on a new project
  As a developer
  I need to be able to create a new flow

  Scenario:
    Given I am authenticated
    When I send a flow creation request
    Then the flow is successfully saved
