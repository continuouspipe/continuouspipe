Feature:
  In order to achieve continuous deployment
  As a developer
  The river should start tides after pushing a commit to GitHub

  Scenario:
    Given I have a flow
    When a push webhook is received
    Then the created tide UUID should be returned
    And the tide should be created
