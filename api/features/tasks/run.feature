Feature:
  In order to run proper tests on my environment
  As a developer
  I want to be able to run arbitrary commands in images

  Scenario:
    Given a run task is started with an image name
    When the run failed
    Then the run task should be failed

  Scenario:
    Given a run task is started with an image name
    When the run succeed
    Then the run task should be successful

  Scenario:
    Given there is 1 application images in the repository
    When a build and run task is started with a service name
    And the build succeed
    Then the run task should be running
    And a run request should be sent

