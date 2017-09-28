Feature:
  In order to being notified when the system misbehaves
  As an administrator
  I need to see the exceptions in the log

  Scenario: Not found HTTP exceptions should be logged as debug level messages
    When a user opens a non-existent page
    Then I should see a not found exception in the logs with "debug" level
    And the number of not found exceptions in the log should be 1

  Scenario: Log the access denied exceptions as info level messages
    Given I am authenticated
    When I try to access an URL that I am not allowed to open
    Then I should see an access denied exception in the logs with "info" level
    And the number of access denied exceptions in the log should be 1
