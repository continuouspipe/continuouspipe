Feature:
  In order to debug the failing tides
  As a developer
  I need to be able to see detailed logs of the river

  Background:
    Given there is 1 application images in the repository

  Scenario: See the title of the started task
    When a tide is started with a deploy task called "my_deploy_task"
    Then a 'Deploying environment (my_deploy_task)' log should be created

  Scenario:
    Given a tide is started with a deploy task
    When the deployment succeed
    Then the 'Deploying environment (task0)' log should be successful

  Scenario:
    Given a tide is started with a deploy task
    When the deployment failed
    Then the 'Deploying environment (task0)' log should be failed
