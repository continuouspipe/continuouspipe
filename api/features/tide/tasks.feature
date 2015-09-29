Feature:
  In order to have customizable workflow
  As a developer
  I want to create flows composed of tasks, and these tasks are executed in a tide

  Background:
    Given there is 1 application images in the repository

  Scenario:
    When a tide is started with a build task
    Then the build task should be started

  Scenario:
    When a tide is started with a build and deploy task
    Then the build task should be started
    And the deploy task should not be started

  Scenario:
    When a tide is started with a build and deploy task
    And all the image builds are successful
    And the build task should not be running
    Then the deploy task should be started

  Scenario:
    When a tide is started with a build and deploy task
    And the build task failed
    Then the tide should be failed
    And the deploy task should not be started

  Scenario: I can have different tasks of the same time in a flow
    Given I tide is started with the following configurations:
      | name   | configuration                             |
      | build  | {"services": []}                          |
      | run    | {"image": "foo", "commands": ["bin/foo"]} |
      | deploy | {"providerName": "foo", "services": []}   |
      | run    | {"image": "bar", "commands": ["bin/bar"]} |
      | deploy | {"providerName": "bar", "services": []}   |
    When the build task succeed
    And the run succeed
    And the deploy task succeed
    Then the second run task should be running
    And the second deploy task should be pending
