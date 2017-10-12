Feature:
  In order to continue work after a successful deployment
  I want to receive a notification when the deployment is finish

  Scenario: A notification is sent when the deployment is successful
    Given I have a running deployment
    When the deployment is successful
    Then one notification should be sent back

  Scenario: A notification is sent when the deployment fail
    Given I have a running deployment
    When the deployment is failed
    Then one notification should be sent back

  Scenario: The notification needs to be retried
    Given I have a running deployment
    And the first notification will fail
    When the deployment is successful
    Then one notification should be successfully sent
