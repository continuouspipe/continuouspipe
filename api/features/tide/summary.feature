Feature:
  In order to have an overview of the running or ran tide
  As a developer
  I want to be able to get the summary of the tide and all the useful informations from it

  Background:
    Given I am authenticated

  Scenario: I can get the summary of a given tide
    Given a tide is created
    And the tide failed
    When I ask the summary of the tide
    Then I should see that the tide is failed

  @wip
  Scenario: If there's a deploy task, I should see the deployed components
    Given a tide is created with a deploy task
    And the deployment succeed
    When I ask the summary of the tide
    Then I should see the list of the deployed services and their addresses
