Feature:
  In order to inform the project team
  As a user
  I want to be able to receive various Slack notifications

  Scenario: By default, it send a notification for every step
    Given I have a flow with the following configuration:
    """
    tasks:
        - {build: {services: []}}

    notifications:
        my_notification:
            slack:
                webhook_url: https://hooks.slack.com/services/1/2/3
    """
    When a tide is started for the branch "my/branch"
    And the tide is successful
    Then a Slack pending notification should have been sent
    And a Slack running notification should have been sent
    And a Slack success notification should have been sent

  Scenario: By default, it send a notification on failure of a tide
    Given I have a flow with the following configuration:
    """
    tasks:
        - {build: {services: []}}

    notifications:
        my_notification:
            slack:
                webhook_url: https://hooks.slack.com/services/1/2/3
    """
    When a tide is started for the branch "my/branch"
    And the tide failed
    Then a Slack failure notification should have been sent

  Scenario: We can manually set when the notification should be sent
    Given I have a flow with the following configuration:
    """
    tasks:
        - {build: {services: []}}

    notifications:
        my_notification:
            slack:
                webhook_url: https://hooks.slack.com/services/1/2/3
            when:
                - failure
    """
    When a tide is started for the branch "my/branch"
    And the tide is successful
    Then a Slack success notification should not have been sent

  Scenario: We can manually set when the notification should be sent
    Given I have a flow with the following configuration:
    """
    tasks:
        - {build: {services: []}}

    notifications:
        my_notification:
            slack:
                webhook_url: https://hooks.slack.com/services/1/2/3
            when:
                - failure
    """
    When a tide is started for the branch "my/branch"
    And the tide failed
    Then a Slack failure notification should have been sent

  Scenario: We can manually set when the notification about run should be sent
    Given I have a flow with the following configuration:
    """
    tasks:
        - {build: {services: []}}

    notifications:
        my_notification:
            slack:
                webhook_url: https://hooks.slack.com/services/1/2/3
            when:
                - running
    """
    When a tide is started for the branch "my/branch"
    Then a Slack running notification should have been sent

  Scenario: The default GitHub commit status should still be configured
    Given I have a flow with the following configuration:
    """
    tasks:
        - {build: {services: []}}

    notifications:
        my_notification:
            slack:
                webhook_url: https://hooks.slack.com/services/1/2/3
            when:
                - success
    """
    When a tide is started for the branch "my/branch"
    And the tide is successful
    Then a Slack success notification should have been sent
    And the GitHub commit status should be "success"
