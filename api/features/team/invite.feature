Feature:
  In order to collaborate with other users on a project
  As a team member
  I want to be able to invite non-registered users

  Background:
    Given I am authenticated as user "samuel"
    And there is a team "my-team"
    And the user "samuel" is administrator of the team "my-team"

  @smoke
  Scenario: I can add a user by its email
    Given there is a user "someone"
    And the email of the user "someone" is "email@exmaple.com"
    When I add the user "email@exmaple.com" in the team "my-team"
    Then the user should be added to the team
    And I can see the user "someone" in the team "my-team"
