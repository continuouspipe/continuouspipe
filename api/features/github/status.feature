Feature:
  In order to have a feedback on GitHub
  As a developer
  I want to see the tide status on the GitHub interface

  Background:
    Given there is 1 application images in the repository

  Scenario:
    When a tide is started with a build task
    Then the GitHub commit status should be "pending"

  Scenario:
    Given a tide is started with a build task
    When the tide failed
    Then the GitHub commit status should be "failure"

  Scenario:
    Given a tide is started with a build task
    When the tide is successful
    Then the GitHub commit status should be "success"
