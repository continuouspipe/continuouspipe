Feature:
  In order to refer to subscriptions
  As a user
  I want to be able to list subscriptions

  Background:
    Given there is a user "samuel"

  Scenario: I can see subscriptions for a billing profile
    Given I am authenticated as user "samuel"
    And there is a billing profile "00000000-0000-0000-0000-000000000001" for the user "samuel"
    And the billing account "00000000-0000-0000-0000-000000000001" have the following subscriptions:
      | plan        | quantity | state  |
      | single-user | 10       | active |
    When I request the subscriptions for billing profile "00000000-0000-0000-0000-000000000001"
    Then I should see the following subscriptions:
      | plan        |
      | single-user |

    Scenario: I cannot see other users subscriptions for a billing profile
      Given I am authenticated as user "samuel"
      And there is a user "mike"
      And there is a billing profile "00000000-0000-0000-0000-000000000000" for the user "mike"
      And the billing account "00000000-0000-0000-0000-000000000000" have the following subscriptions:
        | plan        | quantity | state  |
        | single-user | 10       | active |
      When I request the subscriptions for billing profile "00000000-0000-0000-0000-000000000000"
      Then I should not be authorized to view the subscriptions

