Feature:
  In order to allow other services to use accounts
  As a system
  I want to expose the users' account through an API

  Background:
    Given there is a user "samuel"
    And there is a connected GitHub account "00000000-0000-0000-0000-000000000000" for the user "samuel"
    And there is connected Google account "00000000-0000-0000-0000-000000000001" for the user "samuel"
    And there is connected Google account "00000000-0000-0000-0000-000000000002" for the user "kieren"

  Scenario: I can see only my accounts
    Given I am authenticated as user "samuel"
    When I request the list of my accounts
    Then I should see the GitHub account "00000000-0000-0000-0000-000000000000"
    And I should see the Google account "00000000-0000-0000-0000-000000000001"
    And I should not see the Google account "00000000-0000-0000-0000-000000000002"
