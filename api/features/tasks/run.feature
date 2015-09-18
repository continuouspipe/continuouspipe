Feature:
  In order to run proper tests on my environment
  As a developer
  I want to be able to run arbitrary commands in images

  Background:
    Given there is 1 application images in the repository

  Scenario:
    When a run task is started
    Then a run request should be sent

  Scenario:
    Given a run task is started
    When the run failed
    Then the run task should be failed

  Scenario:
    Given a run task is started
    When the run succeed
    Then the run task should be successful
