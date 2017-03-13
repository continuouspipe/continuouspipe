Feature: Tides per hour limitation per team

  Background:
    Given I am authenticated as user "zsolt"
    And there is a team "bar"
    And the user "zsolt" is in the team "bar"
    And there is a billing profile "00000000-0000-0000-0000-000000000000" for the user "zsolt" with "20" tides per hour
    And the team "bar" is linked to the billing profile "00000000-0000-0000-0000-000000000000"

  Scenario: Limitations could be retrieved
    When I request the limitations for the team "bar"
    Then the tides per hour limit for the team "bar" should be "20"
