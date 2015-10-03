Feature:
  In order to achieve continuous deployment
  As a developer
  The river should start a tide after some events coming from GitHub

  @smoke
  Scenario: By default, a tide should be started when the developer pushes
    Given I have a flow
    When a push webhook is received
    Then the created tide UUID should be returned
    And the tide should be created
