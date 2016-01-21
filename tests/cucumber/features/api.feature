Feature:
  In order to create or update logs
  As a developer
  I want to be able to push logs through an API

  Scenario: I create a log
    Given I have an empty log "123456"
    When I send a POST request to "/api/logs" containing:
    """
    {
      "type": "text",
      "contents": "Welcome !",
      "parent": "123456"
    }
    """
    Then I should see "Welcome" under the log "123456"

  Scenario: I can update an existing log
    Given I have an empty log "123456"
    And I have a text log containing "Welcome" as child of "123456" that have the identifier "23456"
    When I send a PUT request to "/api/logs/23456" containing:
    """
    {
      "contents": "Bonjour"
    }
    """
    Then I should not see "Welcome" under the log "123456"
    Then I should see "Bonjour" under the log "123456"

