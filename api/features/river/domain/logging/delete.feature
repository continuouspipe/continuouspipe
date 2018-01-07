Feature:
  In order to debug the failing tides
  As a developer
  I need to be able to see detailed logs of the river

  Scenario: See the title of the started task
    When a tide is started with a delete task called "my_delete_task"
    Then a 'Deleting environment (my_delete_task)' log should be created
