Feature:
  In order to debug the failing tides
  As a developer
  I need to be able to see detailed logs of the river

  Scenario: See the title of the started task
    When a tide is started with a wait task called "my_wait_task"
    Then a 'Running wait task "my_wait_task": waiting status "one" to be "two"' log should be created
