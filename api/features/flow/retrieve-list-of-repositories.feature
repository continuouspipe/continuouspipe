Feature:
  In order to choose a repository to create a flow
  As a developer
  I need to be able to retrieve a list of repositories

  Scenario: List developer's repositories
    Given I have the following repositories:
      | repository     |
      | dock-cli       |
      | continuouspipe |
    And I am authenticated
    When I send a request to list my repositories
    Then I should receive the following list of repositories:
      | repository     |
      | dock-cli       |
      | continuouspipe |

  Scenario: List repositories for an organisation
    Given the following repositories exist for organisations:
      | organisation | repository            |
      | inviqa       | crazy-project         |
      | inviqa       | another-crazy-project |
      | competitor   | mostly-bad-code       |
    And I am authenticated
    When I send a request to list repositories of "inviqa"
    Then I should receive the following list of repositories:
      | repository            |
      | crazy-project         |
      | another-crazy-project |
