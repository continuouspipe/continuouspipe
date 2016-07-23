Feature:
  In order to collaborate with other users on a project
  As a team member
  I want to be able to invite non-registered users

  Background:
    Given I am authenticated as user "samuel"
    And there is a team "my-team"
    And the user "samuel" is administrator of the team "my-team"
    And The user "invited" is in the white list

  Scenario: I can invite a user to a team
    When I invite the user "user@example.com" to the team "my-team"
    Then the invitation for the user "user@example.com" should be created

  @smoke
  Scenario: Transforms invitations when login-in
    Given the user with email "user@example.com" was invited to join the team "my-team"
    When the user "invited" with email "user@example.com" login
    Then the user "invited" should be in the team "my-team"

  Scenario: Invite a user to be administrator of a team
    Given the user with email "user@example.com" was invited to be administrator of the team "my-team"
    When the user "invited" with email "user@example.com" login
    Then the user "invited" should be in the team "my-team"
    And the user "invited" should be administrator of the team "my-team"
