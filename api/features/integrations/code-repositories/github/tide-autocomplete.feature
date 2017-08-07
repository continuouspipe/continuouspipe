Feature:
  In order to quickly start a manual tide
  As a system
  I want to be able to get a list of branches

  Background:
    Given I am authenticated as "samuel"
    And the team "samuel" exists
    And I have a flow in the team "samuel"
    And the user "samuel" is "USER" of the team "samuel"

  Scenario: I can list branches from a Github account in order to auto complete when manually creating a tide
    Given the GitHub account "sroze" has the installation "0000"
    And the token of the GitHub installation "0000" is "1234"
    And I have a flow "00000000-0000-0000-0000-000000000000" with a GitHub repository "docker-php-example" owned by "sroze"
    And the following branches exists in the github repository:
      | name    |
      | master  |
      | develop |
    When I request the account's branches for the flow "00000000-0000-0000-0000-000000000000"
    Then I should see the following branches:
      | name |
      | master  |
      | develop  |
