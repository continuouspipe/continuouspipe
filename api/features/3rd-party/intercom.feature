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

  Scenario: Do not record the first login when user already exists
    Given The user "existing-user" is in the white list
    And there is a user "existing-user"
    When a user login with GitHub as "existing-user"
    Then an intercom event "first-login" should not be created

  Scenario: Remove the lead if it exists when first login
    Given The user "new-user" is in the white list
    When the user "new-user" with email "new-user@example.com" login
    Then an intercom lead should be merged into the user "new-user@example.com"

  Scenario: Update user's companies when adding to a team
    Given there is a user "another" with email "user@example.com"
    When I add the user "another" in the team "my-team"
    Then an intercom user "another" should be updated with its companies
    And an intercom event "added-to-team" should be created
    And an intercom message should have been sent to the email "user@example.com"

  Scenario: Update user's companies when removing from a team
    When I remove the user "samuel" in the team "my-team"
    Then an intercom user "samuel" should be updated with its companies
    And an intercom event "removed-from-team" should be created

  Scenario: User created event when the user created a team
    When I create a team "new-team"
    Then an intercom event "created-team" should be created
    And an intercom event "added-to-team" should not be created

  Scenario: Add early access code as a tag to the user after login
    Given the user opens the link of the early access program and enter the code "CODE-001"
    When the user "geza" try to authenticate himself with GitHub
    Then an intercom tag "CODE-001" should be created for the user "geza"

  Scenario: It successfully login a user even if intercom is not working
    Given the user opens the link of the early access program and enter the code "CODE-001"
    And the intercom API will throw an exception
    When the user "geza" try to authenticate himself with GitHub
    Then the authentication should be successful
