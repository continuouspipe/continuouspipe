Feature:
  In order to have my pipeline communicating with other systems
  As a integration engineer
  I want to be able to configure a web-hook sent to a 3rd party system

  Scenario: It is a task, that sends the tide's informations via HTTP
    Given I have a flow with the following configuration:
    """
    tasks:
        - web_hook:
              url: https://example.com/my-webhook
    """
    When a tide is started for the branch "master"
    Then a web-hook should be sent to "https://example.com/my-webhook"
    And the web-hook body should contain the code reference with the branch "master"

  Scenario: It contains the deployed environments
    Given I have a flow with the following configuration:
    """
    tasks:
        deploy:
            deploy:
                cluster: foo
                services:
                    app:
                        specification:
                            source:
                                image: nginx

        some_testing_tool:
            web_hook:
                url: http://example.com/another-webhook
    """
    When a tide is started
    And the deployment succeed with the following public address:
      | name | address |
      | app  | 1.2.3.4  |
    Then a web-hook should be sent to "http://example.com/another-webhook"
    And the web-hook should contain the deployed environment "app" that have the address "1.2.3.4"

  Scenario: If the web-hook succeed, it succeed the task and therefore the tide
    Given I have a flow with the following configuration:
    """
    tasks:
        - web_hook:
              url: https://example.com/my-webhook
    """
    When a tide is started for the branch "master"
    Then a web-hook should be sent to "https://example.com/my-webhook"
    And the tide should be successful
    And a 'Calling web-hook (task0)' log should be created

  Scenario: If the web-hook failed, it fails the task and therefore the tide
    Given I have a flow with the following configuration:
    """
    tasks:
        - web_hook:
              url: https://example.com/my-webhook
    """
    And the web-hook will fail
    When a tide is started for the branch "master"
    Then the tide should be failed
    And a 'Sending webhook to "https://example.com/my-webhook" failed: This is planned to fail' log should be created

  Scenario: If the web-hook failed, it fails the task and therefore the tide
    Given I have a flow with the following configuration:
    """
    tasks:
        integration:
            web_hook:
                url: ${WEBHOOK_URL}
    """
    And the web-hook will fail
    When a tide is started for the branch "master"
    Then the tide should be failed
