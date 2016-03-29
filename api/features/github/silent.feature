Feature:
  In order to test Continuous-Pipe on repositories without impact on the current development workflow
  As a developer
  I want to be able to activate a silent mode that won't displays anything on GitHub

  Background:
    Given there is 1 application images in the repository
    And I have a flow with the following configuration:
    """
    silent: true
    """

  Scenario: No GitHub commit status
    When a tide is started with a build task
    Then the GitHub commit status should not be set

  Scenario: No comment on PR
    When a tide is started with a deploy task
    And the pull-request #1 contains the tide-related commit
    When the deployment succeed
    Then the addresses of the environment should not be commented on the pull-request
