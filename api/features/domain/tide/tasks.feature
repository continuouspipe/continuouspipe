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

  Scenario: The tide should be finished when all the tasks are finished
    Given a tide is started with a build and deploy task
    When all the image builds are successful
    And the deployment succeed
    Then the tide should be successful

  Scenario: I can have different tasks of the same time in a flow
    Given a tide is started with the following configurations:
      | type   | configuration                                                    |
      | build  | {"services": []}                                                 |
      | run    | {"cluster": "foo", "image": "foo", "commands": ["bin/foo"]} |
      | deploy | {"cluster": "foo", "services": []}                          |
      | run    | {"cluster": "foo", "image": "bar", "commands": ["bin/bar"]} |
      | deploy | {"cluster": "bar", "services": []}                          |
    When the build task succeed
    And the first run succeed
    And the first deploy succeed
    Then the second run task should be running
    And the second deploy task should be pending

  Scenario: I can have different tasks of the same time in a flow
    Given a tide is started with the following configurations:
      | type   | configuration                                                    |
      | build  | {"services": []}                                                 |
      | run    | {"cluster": "foo", "image": "foo", "commands": ["bin/foo"]} |
      | deploy | {"cluster": "foo", "services": []}                          |
      | run    | {"cluster": "foo", "image": "bar", "commands": ["bin/bar"]} |
      | deploy | {"cluster": "bar", "services": []}                          |
    When the build task succeed
    And the first run succeed
    And the first deploy succeed
    And the second run task succeed
    Then the second deploy task should be running

  Scenario: I can have different tasks of the same time in a flow
    Given a tide is started with the following configurations:
      | type   | configuration                                                    |
      | build  | {"services": []}                                                 |
      | deploy | {"cluster": "foo", "services": []}                          |
      | run    | {"cluster": "foo", "image": "bar", "commands": ["bin/bar"]} |
      | deploy | {"cluster": "bar", "services": []}                          |
    When the build task succeed
    And the first deploy succeed
    And the first run succeed
    Then the second deploy task should be running

  Scenario: A run is blocking until the other other is ran
    Given a tide is started with the following configurations:
      | name  | type   | configuration                                               |
      | build | build  | {"services": []}                                            |
      | run1  | run    | {"cluster": "foo", "image": "bar", "commands": ["bin/bar"]} |
      | run2  | run    | {"cluster": "foo", "image": "bar", "commands": ["bin/bar"]} |
    When the build task succeed
    Then the task named "run1" should be running
    And the task named "run2" should be pending

  Scenario: A failing run will fail the tide
    Given a tide is started with the following configurations:
      | name  | type   | configuration                                               |
      | build | build  | {"services": []}                                            |
      | run1  | run    | {"cluster": "foo", "image": "bar", "commands": ["bin/bar"]} |
      | run2  | run    | {"cluster": "foo", "image": "bar", "commands": ["bin/bar"]} |
    When the build task succeed
    And the first run failed
    And the task named "run2" should be pending
    And the tide should be failed

  Scenario: It should only the task after the skipped task
    Given a tide is started with the following configurations:
      | name  | type   | configuration                                               | filter                            |
      | build | build  | {"services": []}                                            | code_reference.branch != 'master' |
      | run1  | run    | {"cluster": "foo", "image": "bar", "commands": ["bin/bar"]} |                                   |
      | run2  | run    | {"cluster": "foo", "image": "bar", "commands": ["bin/bar"]} |                                   |
    Then the task named "build" should be skipped
    Then the task named "run1" should be running
    Then the task named "run2" should be pending

  Scenario: It should succeed the first build only
    Given there is 1 application images in the repository
    And a tide is started with the following configurations:
      | name   | type   | configuration |
      | build1 | build  | {}            |
      | build2 | build  | {}            |
      | build3 | build  | {}            |
    When the first build task succeed
    Then the task named "build1" should be successful
    And the task named "build2" should be running
    And the task named "build3" should be pending
