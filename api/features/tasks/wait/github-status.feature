Feature:
  In order to synchronize the tide with different external events
  As a developer
  I want to be able to block until something external append

  Scenario: The task is waiting an external event before going to next tasks
    Given I have a flow with the following configuration:
    """
    tasks:
        - wait:
              status:
                  context: scrutinizer
                  state: success
        - build:
              services: []
    """
    And a tide is started
    Then the tide should be running
    And the build task should not be running

  Scenario: If the received status is not related to the tide, it should do nothing
    Given I have a flow with the following configuration:
    """
    tasks:
        - wait:
              status:
                  context: scrutinizer
                  state: success
        - build:
              services: []
    """
    And a tide is started
    When a status webhook is received with the context "scrutinizer" and the value "success" for a different code reference
    Then the tide should be running
    And the build task should not be running

  Scenario: If the received status value is different than the expected one, the wait task should be failed
    Given I have a flow with the following configuration:
    """
    tasks:
        - wait:
              status:
                  context: scrutinizer
                  state: success
    """
    And a tide is started
    When a status webhook is received with the context "scrutinizer" and the value "failure"
    Then the wait task should be failed
    And the tide should be failed

  Scenario: If the received status context is different than the expected one, the task should be still running
    Given I have a flow with the following configuration:
    """
    tasks:
        - wait:
              status:
                  context: scrutinizer
                  state: success
    """
    And a tide is started
    When a status webhook is received with the context "continuous-pipe" and the value "success"
    Then the wait task should be running
    And the tide should be running

  Scenario: If the received status matches the configuration, the tide should be created
    Given I have a flow with the following configuration:
    """
    tasks:
        - wait:
              status:
                  context: scrutinizer
                  state: success
    """
    And a tide is started
    When a status webhook is received with the context "scrutinizer" and the value "success"
    Then the wait task should be successful
