Feature:
  In order to be able to inspect and help debugging various clients
  As a ContinuousPipe buddy
  I need to be able to have access to everything

  Background:
    Given there is a user "the-ghost"
    And the user "the-ghost" have the role "ROLE_GHOST"
    And I am authenticated as user "the-ghost"

  Scenario: As a Ghost user, I can get credentials buckets
    And there is a bucket "00000000-0000-0000-0000-000000000000"
    When I ask the list of the GitHub tokens in the bucket "00000000-0000-0000-0000-000000000000"
    Then I should receive a list

  Scenario: As a Ghost user, I can get team details
    Given there is a team "foo"
    When I request the details of team "foo"
    Then I should see the team details
