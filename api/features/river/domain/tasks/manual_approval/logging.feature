Feature:
  In order to control the manual approval
  The system should create specific logs in order to allow user action

  Background:
    Given I am authenticated as "samuel"
    And the team "samuel" exists
    And the user "samuel" is "USER" of the team "samuel"

  Scenario: It creates a "manual_approval" log
    Given I have a flow with the following configuration:
    """
    tasks:
        - manual_approval: ~
    """
    When a tide is started
    Then a "Waiting manual approval" log should be created
    And a log of type "manual_approval" should be created under the log "Waiting manual approval"
    And a log of type "manual_approval" should contain the following attributes:
      | status  |
      | pending |

  Scenario: It updates the status of the logs of an approved task
    Given I have a flow with the following configuration:
    """
    tasks:
        - manual_approval: ~
    """
    When a tide is started
    And I approve the task
    Then the "Waiting manual approval" log should be successful
    And the log of type "manual_approval" should be successful
    And a log of type "manual_approval" should contain the following attributes:
      | status  | choice_user |
      | success | samuel      |

  Scenario: It updates the status of the logs of an rejected task
    Given I have a flow with the following configuration:
    """
    tasks:
        - manual_approval: ~
    """
    When a tide is started
    And I reject the task
    Then the "Waiting manual approval" log should be failed
    And a log of type "manual_approval" should contain the following attributes:
      | status  | choice_user |
      | failure | samuel      |
