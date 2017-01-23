Feature:
  In order to refer to the billing profiles
  As a user
  I want to be able to list my own billing profiles

  Background:
    Given there is a user "samuel"
    And there is a billing profile "00000000-0000-0000-0000-000000000000" for the user "samuel"
    And there is a billing profile "00000000-0000-0000-0000-000000000001" for the user "kieren"

  Scenario: I can see my billing profile
    Given I am authenticated as user "samuel"
    When I request my billing profile
    Then I should see the billing profile "00000000-0000-0000-0000-000000000000"

  Scenario: No user profile is a 404
    Given I am authenticated as user "unknown"
    When I request my billing profile
    Then I should see the billing profile to be not found
