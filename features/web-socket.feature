Feature:
  In order to increase the speed of log gathering
  As a developer
  I want to be able to push logs through WebSocket

  Scenario: I can connect to the WebSocket and send a message
    Given I have an empty log "123456"
    When I send the following message through the WebSocket:
    """
    {
      "action": "create",
      "body": {
        "type": "text",
        "contents": "Welcome !",
        "parent": "123456"
      }
    }
    """
    Then I should see "Welcome" under the log "123456"

  Scenario: I can update log through WebSocket
    Given I have an empty log "123456"
    And I have a text log containing "Welcome" as child of "123456" that have the identifier "23456"
    When I send the following message through the WebSocket:
    """
    {
      "action": "update",
      "id": "23456",
      "body": {
        "contents": "Bonjour"
      }
    }
    """
    Then I should not see "Welcome" under the log "123456"
    Then I should see "Bonjour" under the log "123456"
