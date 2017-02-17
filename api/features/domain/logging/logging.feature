Feature:
  In order to being notified when the system misbehaves
  As an administrator
  I need to see the exceptions in the log

  Scenario: Not found HTTP exceptions should be logged as debug level messages
    When a user opens a non-existent page
    Then I should see a not found exception in the logs with "debug" level
    And the number of not found exceptions in the log should be 1
