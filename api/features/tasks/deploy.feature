Feature:
  In order to have an environment running
  As a developer
  I want the environment to be deploy

  Background:
    Given there is 2 application images in the repository

  Scenario:
    When a deploy task is started
    Then the deployment should be started

  Scenario:
    Given a deploy task is started
    When the deployment failed
    Then the task should be failed

  Scenario:
    Given a deploy task is started
    When the deployment succeed
    Then the task should be successful
