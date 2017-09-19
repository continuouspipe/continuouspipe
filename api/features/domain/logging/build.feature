Feature:
  In order to debug the failing tides
  As a developer
  I need to be able to see detailed logs of the river

  Background:
    Given there is 1 application images in the repository

  Scenario: See the title of the started task
    When a tide is started with a build task called "my_build_task"
    Then a 'Running build task "my_build_task"' log should be created

  Scenario:
    When a tide is started with a build task
    And all the image builds are successful
    Then the 'Running build task "build"' log should be successful

  Scenario:
    When a tide is started with a build task
    And the builds are failing
    Then the 'Running build task "build"' log should be failed
