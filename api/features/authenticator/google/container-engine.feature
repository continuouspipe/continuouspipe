Feature:
  In order to explore Google Compute's resources
  As a user
  I want to be able to explore the list of the resources I have access with my Google account

  Background:
    Given I am authenticated as user "samuel"
    And the project "my-project" have the following zones:
      | name           |
      | europe-west1-b |
      | europe-west1-c |

  @smoke
  Scenario: I can list my Google Compute projects
    Given there is connected Google account "dd5d98a6-a11f-11e6-80f5-76304dec7eb7" for the user "samuel"
    And the Google account "dd5d98a6-a11f-11e6-80f5-76304dec7eb7" have the following Google Compute projects:
      | projectId       | name           |
      | continuous-pipe | ContinuousPipe |
      | something-else  | Something else |
    When I request the list of Google project for the account "dd5d98a6-a11f-11e6-80f5-76304dec7eb7"
    Then I should see the project "continuous-pipe"
    And I should see the project "something-else"

  Scenario: I can't list somebody else' accounts
    Given there is connected Google account "dd5d98a6-a11f-11e6-80f5-76304dec7eb7" for the user "somebody-else"
    And the Google account "dd5d98a6-a11f-11e6-80f5-76304dec7eb7" have the following Google Compute projects:
      | projectId       | name           |
      | continuous-pipe | ContinuousPipe |
    When I request the list of Google project for the account "dd5d98a6-a11f-11e6-80f5-76304dec7eb7"
    Then I should be forbidden to see this account

  Scenario: I can list my Google Container Engine clusters
    Given there is connected Google account "dd5d98a6-a11f-11e6-80f5-76304dec7eb7" for the user "samuel"
    And there is a project "my-project" in the account "dd5d98a6-a11f-11e6-80f5-76304dec7eb7"
    And there is a cluster named "builder-eu-west1-b" in the "europe-west1-b" zone in the project "my-project"
    And there is a cluster named "builder-eu-west1-c" in the "europe-west1-c" zone in the project "my-project"
    When I request the list of the Google Container Engine clusters for the account "dd5d98a6-a11f-11e6-80f5-76304dec7eb7" and the project "my-project"
    Then I should see the cluster "builder-eu-west1-b"
    And I should see the cluster "builder-eu-west1-c"
