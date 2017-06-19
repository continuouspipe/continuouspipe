Feature:
  In order to be able to use ContinuousPipe
  As a user
  I need to be able to subscribe for a number of users

  Background:
    Given there is a user "samuel"
    And the user "samuel" with email "samuel.roze@gmail.com" is authenticated on its account

  Scenario: It automatically creates a billing profile
    When I view the list of billing profiles
    Then I should see that one has been created in the name "samuel"

  Scenario: I can subscribe for a billing profile
    Given there is a billing profile "00000000-0000-0000-0000-000000000000" for the user "samuel"
    When I configure my billing profile "00000000-0000-0000-0000-000000000000"
    Then I should be able to subscribe

  Scenario: I cannot see someone else's billing profile
    Given there is a billing profile "00000000-0000-0000-0000-000000000000" for the user "samuel"
    And there is a user "test"
    And the user "test" with email "test@example.com" is authenticated on its account
    When I configure my billing profile "00000000-0000-0000-0000-000000000000"
    Then I should not be authorized to view that billing profile

  Scenario: I can create a new billing profile
    When I add a billing profile named "New profile"
    Then I should see that one has been created in the name "New profile"

  Scenario: I don't have any subscription, I subscribe with Recurly
    Given there is a billing profile "00000000-0000-0000-0000-000000000000" for the user "samuel"
    When I subscribe for 10 users to the profile "00000000-0000-0000-0000-000000000000"
    Then I should be redirected to the Recurly subscription page of the account "00000000-0000-0000-0000-000000000000"

  Scenario: I am redirected to the correct billing profile
    Given there is a billing profile "00000000-0000-0000-0000-000000000000" for the user "samuel"
    When I subscribe for 10 users to the profile "00000000-0000-0000-0000-000000000000"
    And the Recurly subscription is successful
    Then I should be redirected to the page of the billing profile "00000000-0000-0000-0000-000000000000"

  Scenario: Subscription is displayed on the billing page
    Given the billing account "00000000-0000-0000-0000-000000000000" have the following subscriptions:
     | plan        | quantity | state  |
     | single-user | 10       | active |
    And there is a billing profile "00000000-0000-0000-0000-000000000000" for the user "samuel"
    When I view the billing profile "00000000-0000-0000-0000-000000000000"
    Then I should see that my current plan is for 10 users

  Scenario: I do have a subscription, I can cancel it
    Given the billing account "00000000-0000-0000-0000-000000000000" have the following subscriptions:
      | plan        | quantity | state  |
      | single-user | 10       | active |
    And there is a billing profile "00000000-0000-0000-0000-000000000000" for the user "samuel"
    When I view the billing profile "00000000-0000-0000-0000-000000000000"
    Then I should be able to cancel my subscription

  Scenario: I do have a subscription, I cancel it
    Given the billing account "00000000-0000-0000-0000-000000000000" have the following subscriptions:
      | uuid                                 | plan        | quantity | state  |
      | 00000000-1111-1111-1111-000000000000 | single-user | 10       | active |
    And there is a billing profile "00000000-0000-0000-0000-000000000000" for the user "samuel"
    When I cancel my subscription "00000000-1111-1111-1111-000000000000" for the profile "00000000-0000-0000-0000-000000000000"
    Then the subscription "00000000-1111-1111-1111-000000000000" should have been cancelled

  Scenario: I do have a subscription, I update it
    Given the billing account "00000000-0000-0000-0000-000000000000" have the following subscriptions:
      | uuid                                 | plan        | quantity | state  |
      | 00000000-1111-1111-1111-000000000000 | single-user | 1        | active |
    And there is a billing profile "00000000-0000-0000-0000-000000000000" for the user "samuel"
    When I update my subscription "00000000-1111-1111-1111-000000000000" with a quantity of 4 for the profile "00000000-0000-0000-0000-000000000000"
    Then the subscription "00000000-1111-1111-1111-000000000000" should have been updated with a quantity of 4

  Scenario: Subscription usage is displayed on the admin
    Given there is a team "foo"
    And there is a billing profile "00000000-0000-0000-0000-000000000000" for the user "samuel"
    And the following usage is recorded for the team "foo":
      | flow_uuid                            | type | user  | date       |
      | 00000000-0000-0000-0000-000000000000 | push | user1 | -1 day     |
      | 00000000-0000-0000-0000-000000000000 | push | user1 | -2 days    |
      | 00000000-0000-0000-0000-000000000000 | push | user2 | -3 days    |
    And the team "foo" is linked to the billing profile "00000000-0000-0000-0000-000000000000"
    When I view the billing profile "00000000-0000-0000-0000-000000000000"
    Then I should see that my current usage is of 2 active users

  Scenario: I see the billing profiles of the teams I am admin
    Given there is a team "foo"
    And there is a billing profile "00000000-0000-0000-0000-000000000000" for the user "samuel" named "My project foo profile"
    And there is a user "another-user"
    And the user "another-user" is administrator of the team "foo"
    And the team "foo" is linked to the billing profile "00000000-0000-0000-0000-000000000000"
    And the user "another-user" with email "test@example.com" is authenticated on its account
    When I view the list of billing profiles
    Then I should see a billing profile named "My project foo profile"
