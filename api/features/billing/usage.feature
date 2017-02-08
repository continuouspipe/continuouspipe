Feature:
  In order to be able to ensure users are have the correct subscription
  As a system
  I need to be able to calculate correctly the billing profile usage

  Background:
    Given there is a user "samuel"
    And there is a team "foo"
    And there is a team "bar"
    And there is a billing profile "00000000-0000-0000-0000-000000000000" for the user "samuel"
    And the team "foo" is linked to the billing profile "00000000-0000-0000-0000-000000000000"
    And the team "bar" is linked to the billing profile "00000000-0000-0000-0000-000000000000"

  Scenario: Count only once the same user
    Given the following usage is recorded for the team "foo":
      | flow_uuid                            | type | user  | date       |
      | 00000000-0000-0000-0000-000000000000 | push | user1 | -1 day     |
      | 00000000-0000-0000-0000-000000000000 | push | user1 | -2 days    |
    When I calculate the usage of the billing profile "00000000-0000-0000-0000-000000000000"
    Then I should see 1 active users

  Scenario: Count the users across teams
    Given the following usage is recorded for the team "foo":
      | flow_uuid                            | type | user  | date       |
      | 00000000-0000-0000-0000-000000000000 | push | user1 | -1 day     |
      | 00000000-0000-0000-0000-000000000000 | push | user2 | -2 days    |
    And the following usage is recorded for the team "bar":
      | flow_uuid                            | type | user  | date       |
      | 00000000-0000-0000-0000-000000000000 | push | user3 | -1 day     |
      | 00000000-0000-0000-0000-000000000000 | push | user4 | -2 days    |
    When I calculate the usage of the billing profile "00000000-0000-0000-0000-000000000000"
    Then I should see 4 active users

  Scenario: De-duplicate users across teams
    Given the following usage is recorded for the team "foo":
      | flow_uuid                            | type | user  | date       |
      | 00000000-0000-0000-0000-000000000000 | push | user1 | -1 day     |
      | 00000000-0000-0000-0000-000000000000 | push | user2 | -2 days    |
    And the following usage is recorded for the team "bar":
      | flow_uuid                            | type | user  | date       |
      | 00000000-0000-0000-0000-000000000000 | push | user1 | -1 day     |
      | 00000000-0000-0000-0000-000000000000 | push | user3 | -2 days    |
    When I calculate the usage of the billing profile "00000000-0000-0000-0000-000000000000"
    Then I should see 3 active users
