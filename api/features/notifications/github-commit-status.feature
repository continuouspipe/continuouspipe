Feature:
  In order to have a feedback on GitHub
  As a developer
  I want to see the tide status on the GitHub interface

  Background:
    Given there is 1 application images in the repository

  Scenario: Set the pending status
    When a tide is started with a build task
    Then the GitHub commit status should be "pending"

  Scenario: Set the failed status
    Given a tide is started with a build task
    When the tide failed
    Then the GitHub commit status should be "failure"

  Scenario: Set the success status
    Given a tide is started with a build task
    When the tide is successful
    Then the GitHub commit status should be "success"

  Scenario: The description should describe the failure
    Given a tide is started with a build and deploy task
    And the build is failing
    Then the GitHub commit status should be "failure"
    And the GitHub commit status description should be:
    """
    Task "build" failed
    """

  Scenario: I can disable the default GitHub commit status
    Given I have a flow with the following configuration:
    """
    tasks:
        - build: ~

    notifications:
        default:
            github_commit_status: false
    """
    When a tide is started
    Then the GitHub commit status should not be updated
