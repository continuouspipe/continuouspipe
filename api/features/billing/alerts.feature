Feature:
  In order to be aware of the problems related to a team
  As a user
  I want to be alerted if there is any problem

  Background:
    Given I am authenticated as user "samuel"
    And there is a team "foo"
    And the user "samuel" is in the team "foo"
    And there is a billing profile "00000000-0000-0000-0000-000000000000"

  Scenario: It alerts when the team do not have any billing profile
    When I request the alerts of the team "foo"
    Then I should see the "billing-profile-not-found" alert

  Scenario: It do not display and alert when billing profile is active
    Given the team "foo" is linked to the billing profile "00000000-0000-0000-0000-000000000000"
    And the billing profile "00000000-0000-0000-0000-000000000000" with the plan "starter" is "active"
    When I request the alerts of the team "foo"
    Then I should not see the "billing-profile-invalid" alert

  Scenario: It displays an alert if billing profile is not active
    Given the billing profile "00000000-0000-0000-0000-000000000000" with the plan "starter" is "expired"
    And the team "foo" is linked to the billing profile "00000000-0000-0000-0000-000000000000"
    When I request the alerts of the team "foo"
    Then I should see the "billing-profile-invalid" alert

  Scenario: It displays an alert if billing profile has no plan
    Given the billing profile "00000000-0000-0000-0000-000000000000" is "expired"
    And the team "foo" is linked to the billing profile "00000000-0000-0000-0000-000000000000"
    When I request the alerts of the team "foo"
    Then I should see the "billing-profile-invalid" alert
