Feature:
  In order to reduce the misconfiguration errors
  As a user
  I want to be informed that my BitBucket addon wasn't installed

  Background:
    Given I am authenticated as "samuel.roze@gmail.com"
    And the team "samuel" exists
    And the user "samuel.roze@gmail.com" is "user" of the team "samuel"

  Scenario: The bitbucket addon installation is not found
    Given I have a flow "00000000-0000-0000-0000-000000000000" with a BitBucket repository "example" owned by user "sroze"
    When I load the alerts of the flow "00000000-0000-0000-0000-000000000000"
    Then I should see the "bitbucket-addon" alert

  Scenario: The bitbucket addon installation is found
    Given I have a flow "00000000-0000-0000-0000-000000000000" with a BitBucket repository "example" owned by user "sroze"
    And there is the add-on "connection:12345" installed for the user account "sroze"
    When I load the alerts of the flow "00000000-0000-0000-0000-000000000000"
    Then I should not see the "bitbucket-addon" alert
