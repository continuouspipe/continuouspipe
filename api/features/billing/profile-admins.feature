Feature:
  In order to use a common billing profile across users
  As an office person
  I want to be able to add admin users to a billing profile. These other admin users can manage the billing profile, as I do.

  Background:
    Given there is a user "samuel"
    Given there is a user "flick"
    And there is a billing profile "00000000-0000-0000-0000-000000000000"
    And the user "samuel" is administrator of the billing profile "00000000-0000-0000-0000-000000000000"

  Scenario: As a admin, I can add an admin to a billing profile
    Given I am authenticated as user "samuel"
    When I add "flick" as an administrator of the billing profile "00000000-0000-0000-0000-000000000000"
    And I request the billing profile "00000000-0000-0000-0000-000000000000"
    Then I should see "flick" as admin of the billing profile "00000000-0000-0000-0000-000000000000"

  Scenario: As a simple user, I can't touch a billing profile
    Given I am authenticated as user "flick"
    When I add "flick" as an administrator of the billing profile "00000000-0000-0000-0000-000000000000"
    Then I should be forbidden to see this account

  @smoke
  Scenario: I can remove and administrator
    Given I am authenticated as user "samuel"
    And the user "flick" is administrator of the billing profile "00000000-0000-0000-0000-000000000000"
    When I remove "flick" as an administrator of the billing profile "00000000-0000-0000-0000-000000000000"
    And I request the billing profile "00000000-0000-0000-0000-000000000000"
    Then I should not see "flick" as admin of the billing profile "00000000-0000-0000-0000-000000000000"

  Scenario: As an admin, I see the invoices link
    Given I am authenticated as user "samuel"
    And the billing account "00000000-0000-0000-0000-000000000000" have the following subscriptions:
      | plan        | quantity | state  |
      | single-user | 10       | active |
    And I request the billing profile "00000000-0000-0000-0000-000000000000"
    Then I should see the billing profile invoices link
