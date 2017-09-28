Feature:
  In order to debug the failing tides
  As a developer
  I need to be able to see detailed logs of the river

  Scenario: See the title of the started task
    When a tide is started with a wait task called "my_wait_task"
    Then a 'Waiting for status "one" to be "two" (my_wait_task)' log should be created
