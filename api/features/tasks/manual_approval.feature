Feature:
  In order to prevent un-wanted deployments
  As a devops person
  I want to be able to create a pipeline with manual approval

  Background:
    Given I am authenticated as "samuel"
    And the team "samuel" exists
    And the user "samuel" is "USER" of the team "samuel"

  Scenario: The tide keeps running while the choice is not given
    Given I have a flow with the following configuration:
    """
    tasks:
        - manual_approval: ~
    """
    When a tide is started
    Then the tide should be running

  Scenario: The task succeed when it is approved
    Given I have a flow with the following configuration:
    """
    tasks:
        - manual_approval: ~
    """
    When a tide is started
    And I approve the task
    Then the manual approval task should be successful
    And the tide should be successful

  Scenario: The task fail when it is rejected
    Given I have a flow with the following configuration:
    """
    tasks:
        - manual_approval: ~
    """
    When a tide is started
    And I reject the task
    Then the manual approval task should be failed
    And the tide should be failed
