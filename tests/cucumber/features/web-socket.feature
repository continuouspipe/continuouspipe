Feature:
  In order to increase the speed of log gathering
  As a developer
  I want to be able to push logs through WebSocket

  @dev
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
