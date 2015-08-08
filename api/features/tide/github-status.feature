Feature:
  In order to have a feedback on GitHub
  As a developer
  I want to see the tide status on the GitHub interface

  Scenario:
    When a tide is started
    Then the GitHub commit status should be "pending"

  Scenario:
    Given a tide is started
    When the tide failed
    Then the GitHub commit status should be "failure"

  Scenario:
    Given a tide is started
    When the tide is successful
    Then the GitHub commit status should be "success"
