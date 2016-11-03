Feature:
  In order to explore Google Compute's resources
  As a user
  I want to be able to explore the list of the projects I have access with my Google account

  Background:
    Given I am authenticated as user "samuel"

  Scenario: I can list my Google Compute projects
    Given there is connected Google account "dd5d98a6-a11f-11e6-80f5-76304dec7eb7" for the user "sroze"
    And the Google account "dd5d98a6-a11f-11e6-80f5-76304dec7eb7" have the following Google Compute projects:
      | projectId       | name           |
      | continuous-pipe | ContinuousPipe |
      | something-else  | Something else |
    When I request the list of Google project for the account "dd5d98a6-a11f-11e6-80f5-76304dec7eb7"
    Then I should see the project "continuous-pipe"
    And I should see the project "something-else"
