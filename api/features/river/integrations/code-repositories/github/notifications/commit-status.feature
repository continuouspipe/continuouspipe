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

  Scenario: Set the failed status when cancelled
    Given a tide is started with a build task
    When the tide is cancelled
    Then the GitHub commit status should be "failure"

  Scenario: The description should describe the failure
    Given a tide is started with a build and deploy task
    And the build is failing
    Then the GitHub commit status should be "failure"
    And the GitHub commit status description should be:
    """
    Task "build" failed
    """

  Scenario: If the description is too long, it needs to be reduced to 140 characters maximum
    Given a tide is created
    When the tide is failing because "This is a very long reason that is not really supported by GitHub because the number of characters of this reason is more than 140 characters."
    And the GitHub commit status description should be:
    """
    This is a very long reason that is not really supported by GitHub because the number of characters of this reason is more than 140 charac...
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

  Scenario: The default GitHub commit status should still be configured if another notification is configured
    Given I have a flow with the following configuration:
    """
    tasks:
        - {build: {services: []}}

    notifications:
        my_notification:
            slack:
                webhook_url: https://hooks.slack.com/services/1/2/3
            when:
                - success
    """
    When a tide is started for the branch "my/branch"
    And the tide is successful
    Then a Slack success notification should have been sent
    And the GitHub commit status should be "success"
