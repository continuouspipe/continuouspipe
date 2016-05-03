Feature:
  In order to keep the tide order and prevent concurrency problems on the environments
  As a system
  I should run only one concurrent tide per branch

  Background:
    Given there is 1 application images in the repository
    And I have a flow with the following configuration:
    """
    tasks: [{build: {services: []}}]
    """

  @smoke
  Scenario: It should not start the tide if one is already running on the branch
    When the commit "000000000000000000000000000000000000000" is pushed to the branch "master"
    And the tide for commit "000000000000000000000000000000000000000" is tentatively started
    And the commit "111111111111111111111111111111111111111" is pushed to the branch "master"
    And the tide for commit "111111111111111111111111111111111111111" is tentatively started
    Then the tide for the commit "000000000000000000000000000000000000000" should be started
    And the tide for the commit "111111111111111111111111111111111111111" should not be started
    And the start of the pending tides of the branch "master" should be delayed

  Scenario: Two tides of on different branches of the same flow can run in parallel
    When the commit "000000000000000000000000000000000000000" is pushed to the branch "master"
    And the tide for commit "000000000000000000000000000000000000000" is tentatively started
    And the commit "111111111111111111111111111111111111111" is pushed to the branch "develop"
    And the tide for commit "111111111111111111111111111111111111111" is tentatively started
    Then the tide for the commit "000000000000000000000000000000000000000" should be started
    And the tide for the commit "111111111111111111111111111111111111111" should be started

  Scenario: The oldest pending tide should be run
    Given the commit "000000000000000000000000000000000000000" is pushed to the branch "master"
    And the tide for commit "000000000000000000000000000000000000000" is tentatively started
    And the commit "111111111111111111111111111111111111111" is pushed to the branch "master"
    And the tide for commit "111111111111111111111111111111111111111" is tentatively started
    And the commit "222222222222222222222222222222222222222" is pushed to the branch "master"
    And the tide for commit "222222222222222222222222222222222222222" is tentatively started
    When the tide for commit "000000000000000000000000000000000000000" is successful
    Then the tide for the commit "111111111111111111111111111111111111111" should be started
    Then the tide for the commit "222222222222222222222222222222222222222" should not be started
