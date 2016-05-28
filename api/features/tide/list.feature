Feature:
  In order to know what's going on in my flow
  As an API consumer
  I need to be able to get the list of the tides in a flow

  Background:
    Given I am authenticated as "samuel"
    And the team "samuel" exists
    And the user "samuel" is "USER" of the team "samuel"
    And I have a flow with UUID "00000000-0000-0000-0000-000000000000" in the team "samuel"
    And I have a tide "00000000-0000-0000-0000-000000000001"
    And I have a tide "00000000-0000-0000-0000-000000000002"
    And I have a tide "00000000-0000-0000-0000-000000000003"

  Scenario: I can list all the tides
    When I retrieve the list tides of the flow "00000000-0000-0000-0000-000000000000"
    And I should see the tide "00000000-0000-0000-0000-000000000003"
    And I should see the tide "00000000-0000-0000-0000-000000000002"
    Then I should see the tide "00000000-0000-0000-0000-000000000001"

  Scenario: Limit the number of displayed tides
    When I retrieve the list tides of the flow "00000000-0000-0000-0000-000000000000" with a limit of 2 tides
    Then I should see the tide "00000000-0000-0000-0000-000000000003"
    And I should see the tide "00000000-0000-0000-0000-000000000002"
    And I should not see the tide "00000000-0000-0000-0000-000000000001"

  @smoke
  Scenario: Limit and next pages
    When I retrieve the page 2 of the list of tides of the flow "00000000-0000-0000-0000-000000000000" with a limit of 2 tides
    Then I should not see the tide "00000000-0000-0000-0000-000000000003"
    And I should not see the tide "00000000-0000-0000-0000-000000000002"
    And I should see the tide "00000000-0000-0000-0000-000000000001"

  Scenario: Can't list the tides if I'm not in the team
    Given I am authenticated as "another-user"
    When I retrieve the list tides of the flow "00000000-0000-0000-0000-000000000000"
    Then I should be told that I don't have the permissions the list the tides


