Feature:
  In order to debug the failing tides
  As a developer
  I need to be able to see detailed logs of the river

  Scenario: See the title of the started task
    When a tide is started with a run task called "my_run_task"
    Then a 'Running my_run_task' log should be created
