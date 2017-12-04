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
