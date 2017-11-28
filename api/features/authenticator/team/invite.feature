Feature:
  In order to collaborate with other users on a project
  As a team member
  I want to be able to invite non-registered users

  Background:
    Given there is a team "my-team"
    And the user "samuel" is administrator of the team "my-team"
    And there is a user "invited"

  Scenario: I can invite a user to a team
    Given I am authenticated as user "samuel"
    When I invite the user "user@example.com" to the team "my-team"
    Then the invitation for the user "user@example.com" should be created

  Scenario: List the invitations and their status
    Given I am authenticated as user "samuel"
    And the user with email "user@example.com" was invited to be administrator of the team "my-team"
    When I request the list of invitations for the team "my-team"
    Then I should see the invitation for the user with email "user@example.com"

  Scenario: Delete an invitation
    Given I am authenticated as user "samuel"
    And the user with email "user@example.com" was invited to be administrator of the team "my-team"
    When I delete the invitation for the user with email "user@example.com" for the team "my-team"
    And I request the list of invitations for the team "my-team"
    Then I should not see the invitation for the user with email "user@example.com"

  Scenario: Updated list of users and invitations
    Given I am authenticated as user "samuel"
    And the user with email "user@example.com" was invited to join the team "my-team"
    When I request the status of members for the team "my-team"
    Then I should see the invitation for the user with email "user@example.com" in the member status
    And I should see the user "samuel" in the member status

  @smoke
  Scenario: Transforms the invitation with another account
    Given the user with email "user@company.com" was invited to join the team "my-team" with the UUID "11111111-0000-0000-0000-000000000000"
    When the user open the link of the invitation "11111111-0000-0000-0000-000000000000" and authentication with "invited" and "user@example.com"
    Then the user "invited" should be in the team "my-team"

  Scenario: Transforms the invitation as an administrator
    Given the user with email "user@example.com" was invited to be administrator of the team "my-team" with the UUID "11111111-0000-0000-0000-000000000000"
    When the user open the link of the invitation "11111111-0000-0000-0000-000000000000" and authentication with "invited" and "user@example.com"
    Then the user "invited" should be in the team "my-team"
    And the user "invited" should be administrator of the team "my-team"

  Scenario: Do not transforms invitations when login-in
    Given the user with email "user@example.com" was invited to join the team "my-team"
    When the user "invited" with email "user@example.com" login
    Then the user "invited" should not be in the team "my-team"
