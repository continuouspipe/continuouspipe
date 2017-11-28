Feature:
  In order to switch between contexts
  As a user
  I want to be able to list my teams

  Scenario: As a user, I don't see the other teams
    Given I am authenticated as user "samuel"
    And there is a team "foo"
    And there is a team "bar"
    And the user "samuel" is in the team "foo"
    When I request the list of teams
    Then I should see the team "foo" in the team list
    And I should not see the team "bar" in the team list

  Scenario: As a system user, I can see all the teams
    Given there is the system api key "1234"
    And there is a team "bar"
    And there is a team "foo"
    When I request the list of teams with the API key "1234"
    Then I should see the team "foo" in the team list
    And I should see the team "bar" in the team list
