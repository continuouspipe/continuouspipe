Feature:
  In order to track significant events inside the application
  As an administrator
  I need to know what happened and when it happened

  Scenario: Create a log record about new user registration
    When a login with GitHub as "geza" with the token "1234"
    Then the authentication should be successful
    And a record should be added to the audit log storage

  Scenario: Do not log existing user login event
    Given there is a user "geza"
    When a login with GitHub as "geza" with the token "1234"
    Then the authentication should be successful
    And no record should be added to the audit log storage

  Scenario: Create a log record about new project team creation
    When the team "team1" named "The A team" is created
    Then the team should be successfully created
    And a record should be added to the audit log storage

  Scenario: View the recorded audit log
    Given I am authenticated as admin "geza"
    And these records exist in audit log storage
      | Event Type  | Event Date                | Event Name   | Properties       |
      | UserCreated | 2017-09-28T12:53:04+00:00 | foo_happened | property1=value1 |
      | TeamCreated | 2017-09-28T13:22:55+00:00 | bar_happened | property2=value2 |
    When I visit the audit log view page of type UserCreated
    Then I should see these list of audit log records
      | Event Date                | Event Name   | Property1 |
      | 2017-09-28T12:53:04+00:00 | foo_happened | value1    |
    And I should not see these list of audit log records
      | Event Date                | Event Name   | Property1 |
      | 2017-09-28T12:22:55+00:00 | bar_happened | value2    |
