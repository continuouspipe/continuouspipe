Feature:
  In order to be able to use CP for a team of developers
  As a user
  I need to be able to manage a team of users

  Background:
    Given I am authenticated as user "samuel"

  Scenario: I can create a team
    When I create a team "continuous-pipe"
    And I should see the team "continuous-pipe" in my teams list

  Scenario: I can add a user to a team
    Given there is a team "foo"
    And there is a user "bar"
    When I add the user "bar" in the team "foo"
    Then I can see the user "bar" in the team "foo"
