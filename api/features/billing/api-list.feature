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
    When I request my billing profiles
    Then I should see the following billing profiles:
      | uuid                                 |
      | 00000000-0000-0000-0000-000000000000 |

  Scenario: I can see my billing profile
    Given I am authenticated as user "samuel"
    And there is a billing profile "00000000-0000-0000-0000-000000000002" for the user "samuel"
    When I request my billing profiles
    Then I should see the following billing profiles:
      | uuid                                 |
      | 00000000-0000-0000-0000-000000000000 |
      | 00000000-0000-0000-0000-000000000002 |

  Scenario: No user profile is a 404
    Given I am authenticated as user "unknown"
    When I request my billing profiles
    Then I should see the billing profile to be not found

  Scenario: Get a team's billing profile
    Given I am authenticated as user "samuel"
    And there is a team "foo"
    And the user "samuel" is administrator of the team "foo"
    And the team "foo" is linked to the billing profile "00000000-0000-0000-0000-000000000000"
    When I request the billing profile of the team "foo"
    Then I should see that the billing profile is "00000000-0000-0000-0000-000000000000"
