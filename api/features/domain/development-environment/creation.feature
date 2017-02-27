Feature:
  In order to easily get started with a remote environment
  As a user
  I want to be able to create a virtual environment client

  Background:
    Given the team "continuous-pipe" exists
    And there is a user "samuel"
    And the user "samuel" is "user" of the team "continuous-pipe"
    And I have a flow with UUID "00000000-0000-0000-0000-000000000000" in the team "continuous-pipe"

  @smoke
  Scenario: Create an environment for a flow
    Given I am authenticated as "samuel"
    When I create a development environment client named "sroze's environment" for the flow "00000000-0000-0000-0000-000000000000"
    And I request the list of the development environments of the flow "00000000-0000-0000-0000-000000000000"
    Then I should see the development environment "sroze's environment"
