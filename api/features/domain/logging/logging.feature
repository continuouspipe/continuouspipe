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

  Scenario: Able to see the context of an exception in the logs
    Given I am authenticated as "geza"
    And the team "projectX" exists
    And the team "projectY" exists
    And the user "geza" is "USER" of the team "projectX"
    And I have a flow with UUID "00000000-0000-0000-0000-000000000001" in the team "projectY"
    And a tide is started with the UUID "00000000-0000-0000-0000-000000000002"
    When I start an operation with the tide "00000000-0000-0000-0000-000000000002" that fails
    Then I should see a runtime exception in the logs tagged with
      | Tag name | Tag value                            |
      | team     | projectY                             |
      | flow     | 00000000-0000-0000-0000-000000000001 |
      | tide     | 00000000-0000-0000-0000-000000000002 |

  Scenario: Able to see the context of an exception in the logs
    Given I am authenticated as "geza"
    And the team "projectX" exists
    And I have a flow with UUID "00000000-0000-0000-0000-000000000000" in the team "projectX"
    When a webhook is received from GitHub for the flow "00000000-0000-0000-0000-000000000000" that fails
    Then I should see a runtime exception in the logs tagged with
      | Tag name | Tag value                            |
      | team     | projectX                             |
      | flow     | 00000000-0000-0000-0000-000000000000 |

  Scenario: Able to see the context of an exception in the logs
    Given I am authenticated as "geza"
    And the team "projectX" exists
    And I have a flow with UUID "00000000-0000-0000-0000-000000000000" in the team "projectX"
    When a webhook is received from BitBucket for the flow "00000000-0000-0000-0000-000000000000" that fails
    Then I should see a runtime exception in the logs tagged with
      | Tag name | Tag value                            |
      | team     | projectX                             |
      | flow     | 00000000-0000-0000-0000-000000000000 |

  Scenario: Able to see the context of an exception in the logs
    Given I am authenticated as "geza"
    And the team "projectX" exists
    And I have a flow with UUID "00000000-0000-0000-0000-000000000001" in the team "projectX"
    And a tide is started with the UUID "00000000-0000-0000-0000-000000000002"
    When a worker receives a tide command with UUID "00000000-0000-0000-0000-000000000002" that fails
    Then I should see a runtime exception in the logs tagged with
      | Tag name | Tag value                            |
      | team     | projectX                             |
      | flow     | 00000000-0000-0000-0000-000000000001 |
      | tide     | 00000000-0000-0000-0000-000000000002 |
