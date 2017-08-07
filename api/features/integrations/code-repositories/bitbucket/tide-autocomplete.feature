Feature:
  In order to quickly start a manual tide
  As a system
  I want to be able to get a list of branches

  Background:
    Given I am authenticated as "samuel"
    And the team "samuel" exists
    And I have a flow in the team "samuel"

  Scenario: I can list branches from a BitBucket account in order to auto complete when manually creating a tide
    Given the user "samuel" is "USER" of the team "samuel"
    And I have a flow "00000000-0000-0000-0000-000000000000" with a Bitbucket repository "my-example" owned by "samuel"
    And there is the add-on installed for the BitBucket repository "my-example" owned by user "samuel"
    And the following branches exist in the bitbucket repository with slug "my-example" for user "samuel":
      | name    |
      | master  |
      | develop |
    When I request the account's branches for the flow "00000000-0000-0000-0000-000000000000"
    Then I should see the following branches:
      | name |
      | master  |
      | develop  |

  Scenario: I can't list branches when I don't have the correct permissions
    Given the user "samuel" is not in the team "samuel"
    And I have a flow "00000000-0000-0000-0000-000000000000" with a Bitbucket repository "my-example" owned by "samuel"
    And there is the add-on installed for the BitBucket repository "my-example" owned by user "samuel"
    And the following branches exist in the bitbucket repository with slug "my-example" for user "samuel":
      | name    |
      | master  |
      | develop |
    When I request the account's branches for the flow "00000000-0000-0000-0000-000000000000"
    Then I should be told that I don't have the permissions
