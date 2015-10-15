Feature:
  In order to be able to use CP for a team of developers
  As a user
  I need to be able to manage a team of users

  Background:
    Given I am authenticated as user "samuel"

  Scenario: I can create a team
    When I create a team "continuous-pipe"
    Then I should see the team "continuous-pipe" in my teams list
    And the user "samuel" should be administrator of the team "continuous-pipe"

  Scenario: I can add a user to a team if I am administrator
    Given there is a team "foo"
    And the user "samuel" is administrator of the team "foo"
    And there is a user "bar"
    When I add the user "bar" in the team "foo"
    Then the user should be added to the team
    And I can see the user "bar" in the team "foo"

  Scenario: I can't add a user to a team if I am not administrator of this team
    Given there is a team "foo"
    And there is a user "bar"
    When I add the user "bar" in the team "foo"
    Then I should be told that I don't have the authorization

  Scenario: Each team should have a credentials bucket
    When I create a team "new"
    Then the team "new" should have a credentials bucket
