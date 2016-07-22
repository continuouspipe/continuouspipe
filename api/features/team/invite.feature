Feature:
  In order to collaborate with other users on a project
  As a team member
  I want to be able to invite non-registered users

  Background:
    Given I am authenticated as user "samuel"
    And there is a team "my-team"
    And the user "samuel" is administrator of the team "my-team"

  Scenario: I can invite a user to a team
    When I invite the user "user@example.com" to the team "my-team"
    Then the invitation for the user "user@example.com" should be created
