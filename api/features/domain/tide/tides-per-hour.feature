Feature: Starting a tide is limited by usage limits

  Background:
    Given there is 1 application images in the repository
    And I have a flow with the following configuration:
    """
    tasks: [{build: {services: []}}]
    """
    And the team "samuel" exists

  @smoke
  Scenario: Any number of tides could be started if limitation is not configured
    Given the team "samuel" has a "0" tides per hour usage limit
    When the commit "000000000000000000000000000000000000000" is pushed to the branch "master"
    And the tide for the branch "master" and commit "000000000000000000000000000000000000000" is tentatively started
    And the commit "111111111111111111111111111111111111111" is pushed to the branch "develop"
    And the tide for the branch "develop" and commit "111111111111111111111111111111111111111" is tentatively started
    And the commit "222222222222222222222222222222222222222" is pushed to the branch "feature"
    And the tide for the branch "feature" and commit "222222222222222222222222222222222222222" is tentatively started
    Then the tide for the branch "master" and commit "000000000000000000000000000000000000000" should be started
    And the tide for the branch "develop" and commit "111111111111111111111111111111111111111" should be started
    And the tide for the branch "feature" and commit "222222222222222222222222222222222222222" should be started

  @smoke
  Scenario: A tide can be started only if usage is within limits
    Given the team "samuel" has a "1" tides per hour usage limit
    When the commit "000000000000000000000000000000000000000" is pushed to the branch "master"
    And the tide for the branch "master" and commit "000000000000000000000000000000000000000" is tentatively started
    And the commit "111111111111111111111111111111111111111" is pushed to the branch "develop"
    And the tide for the branch "develop" and commit "111111111111111111111111111111111111111" is tentatively started
    And the commit "222222222222222222222222222222222222222" is pushed to the branch "feature"
    And the tide for the branch "feature" and commit "222222222222222222222222222222222222222" is tentatively started
    Then the tide for the branch "master" and commit "000000000000000000000000000000000000000" should be started
    And the tide for the branch "develop" and commit "111111111111111111111111111111111111111" should not be started
    And the tide for the branch "feature" and commit "222222222222222222222222222222222222222" should not be started

  Scenario: A tide can be started only if usage is within limits
    Given the team "samuel" has a "2" tides per hour usage limit
    When the commit "000000000000000000000000000000000000000" is pushed to the branch "master"
    And the tide for the branch "master" and commit "000000000000000000000000000000000000000" is tentatively started
    And the commit "111111111111111111111111111111111111111" is pushed to the branch "develop"
    And the tide for the branch "develop" and commit "111111111111111111111111111111111111111" is tentatively started
    And the commit "222222222222222222222222222222222222222" is pushed to the branch "feature"
    And the tide for the branch "feature" and commit "222222222222222222222222222222222222222" is tentatively started
    Then the tide for the branch "master" and commit "000000000000000000000000000000000000000" should be started
    And the tide for the branch "develop" and commit "111111111111111111111111111111111111111" should be started
    And the tide for the branch "feature" and commit "222222222222222222222222222222222222222" should not be started
