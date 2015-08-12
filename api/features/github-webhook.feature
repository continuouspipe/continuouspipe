Feature:
  In order to achieve continuous deployment
  As a developer
  The river should start tides after GitHub webhooks

  Scenario:
    Given I have a flow
    When a push webhook is received
    Then the created tide UUID should be returned
    And the tide should be created
