Feature:
  In order to save space and costs on the hosting of logs
  As a system
  I want to archive the logs of a tide when it is finished, with a delay.

  Scenario: It archives a successful tide
    Given I have a flow with the following configuration:
    """
    tasks:
        - {build: {services: []}}
    """
    When a tide is started for the branch "my/branch"
    And the tide is successful
    Then the tide log archive command should be delayed

  Scenario: It archives a failed tide
    Given I have a flow with the following configuration:
    """
    tasks:
        - {build: {services: []}}
    """
    When a tide is started for the branch "my/branch"
    And the tide failed
    Then the tide log archive command should be delayed
