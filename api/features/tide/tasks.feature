Feature:
  In order to have customizable workflow
  As a developer
  I want to create flows composed of tasks, and these tasks are executed in a tide

  Scenario:
    Given I have a flow with the build task
    When a tide is started based on that workflow
    Then the build task should be started

  Scenario:
    Given I have a flow with the build and deploy tasks
    When a tide is started based on that workflow
    Then the build task should be started
    And the deploy task should not be started

  Scenario:
    Given I have a flow with the build and deploy tasks
    When a tide is started based on that workflow
    And the build task succeed
    And the build task should not be running
    Then the deploy task should be started

  Scenario:
    Given I have a flow with the build and deploy tasks
    When a tide is started based on that workflow
    And the build task failed
    Then the tide should be failed
    And the deploy task should not be started
