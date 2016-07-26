Feature:
  In order to keep trace and engage customers
  As a system
  I want to integrate and synchronize some data with Intercom

  Background:
    Given I am authenticated as user "samuel"
    And there is a team "my-team"
    And the user "samuel" is administrator of the team "my-team"

  Scenario: Create a lead when inviting a user
    When I invite the user "user@example.com" to the team "my-team"
    Then an intercom lead should be created for the email "user@example.com"
    And an intercom message should have been sent to the lead "user@example.com"

  Scenario: Update or create user when login
    Given The user "samuel" is in the white list
    When a user login with GitHub as "samuel"
    Then an intercom user "samuel" should be created or updated
