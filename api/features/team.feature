Feature:
  In order to be able to use CP for a team of developers
  As a user
  I need to be able to manage a team of users

  Scenario: I can create a team, and should be the owner
    Given I am authenticated as user "samuel"
    When I create a team "continuous-pipe"
    And I should see the team "continuous-pipe" in my teams list
