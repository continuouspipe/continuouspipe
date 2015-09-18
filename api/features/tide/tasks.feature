Feature:
  In order to have customizable workflow
  As a developer
  I want to create flows composed of tasks, and these tasks are executed in a tide

  Background:
    Given there is 1 application images in the repository

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
    And all the image builds are successful
    And the build task should not be running
    Then the deploy task should be started

  Scenario:
    Given I have a flow with the build and deploy tasks
    When a tide is started based on that workflow
    And the build task failed
    Then the tide should be failed
    And the deploy task should not be started

  Scenario: I can have different tasks of the same time in a flow
    Given I have a flow with the following tasks:
      | name   | context                                   |
      | build  | {"image": "foo"}                          |
      | run    | {"service": "foo", "commands": "bin/foo"} |
      | deploy | {"providerName": "foo"}                   |
      | run    | {"service": "bar", "commands": "bin/bar"} |
    When a tide is started based on that workflow
    And the build task succeed
    And the run succeed
    And the deploy task succeed
    Then the second run task should be running
