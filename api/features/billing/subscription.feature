Feature:
  In order to be able to use ContinuousPipe
  As a user
  I need to be able to subscribe for a number of users

  Background:
    Given there is a user "samuel"
    And the user "samuel" with email "samuel.roze@gmail.com" is authenticated on its account

  Scenario: I can subscribe when I don't have any subscription
    When I configure my billing profile
    Then I should be able to subscribe

  Scenario: I don't have any subscription, I subscribe with Recurly
    Given there is a billing profile "00000000-0000-0000-0000-000000000000" for the user "samuel"
    When I subscribe for 10 users
    Then I should be redirected to the Recurly subscription page of the account "00000000-0000-0000-0000-000000000000"

  Scenario: Subscription is displayed on the billing page
    Given the billing account "00000000-0000-0000-0000-000000000000" have the following subscriptions:
     | plan        | quantity | state  |
     | single-user | 10       | active |
    And there is a billing profile "00000000-0000-0000-0000-000000000000" for the user "samuel"
    When I configure my billing profile
    Then I should see that my current plan is for 10 users

  Scenario: I do have a subscription, I can cancel it
    Given the billing account "00000000-0000-0000-0000-000000000000" have the following subscriptions:
      | plan        | quantity | state  |
      | single-user | 10       | active |
    And there is a billing profile "00000000-0000-0000-0000-000000000000" for the user "samuel"
    When I configure my billing profile
    Then I should be able to cancel my subscription

  Scenario: I do have a subscription, I cancel the it
    Given the billing account "00000000-0000-0000-0000-000000000000" have the following subscriptions:
      | uuid                                 | plan        | quantity | state  |
      | 00000000-1111-1111-1111-000000000000 | single-user | 10       | active |
    And there is a billing profile "00000000-0000-0000-0000-000000000000" for the user "samuel"
    When I cancel my subscription "00000000-1111-1111-1111-000000000000"
    Then the subscription "00000000-1111-1111-1111-000000000000" should have been cancelled

  Scenario: I do have a subscription, I upgrade

  Scenario: I do have a subscription, I downgrade
