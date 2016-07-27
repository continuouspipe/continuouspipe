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

  Scenario: Record the first login event when first login
    Given The user "new-user" is in the white list
    When a user login with GitHub as "new-user"
    Then an intercom event "first-login" should be created

  Scenario: Record the first login event when first login
    Given The user "existing-user" is in the white list
    And there is a user "existing-user"
    When a user login with GitHub as "existing-user"
    Then an intercom event "first-login" should not be created

  Scenario: Update user's companies when adding to a team
    Given there is a team "another-team"
    When I add the user "samuel" in the team "another-team"
    Then an intercom user "samuel" should be updated with its companies
    And an intercom event "added-to-team" should be created

  Scenario: Update user's compagnies when removing from a team
    When I remove the user "samuel" in the team "my-team"
    Then an intercom user "samuel" should be updated with its companies
    And an intercom event "removed-from-team" should be created
