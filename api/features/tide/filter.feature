Feature:
  In order to have a better control on tides
  We need to be able to setup filters that will prevent the tide to start

  Scenario: I can prevent the tide to start if the pull-request do not have a given tag
    Given I have a flow with the following configuration:
    """
    filter: "Ready for QA" in pull_request.tags
    """
    When a push webhook is received
    Then the tide should be created
    And the tide should not be started

  Scenario: The tide is started only if a pull-request is created
    Given I have a flow with the following configuration:
    """
    filter: "Ready for QA" in pull_request.tags
    """
    And there is a tide for the commit "1234543564325323454324"
    When a pull-request webhook is received
    Then the tide should be started
