Feature:
  In order to access to GitHub repositories on the behalf of clients
  As an internal service
  I want to get the GitHub installation tokens so I can call GitHub's API

  Scenario: I can get the token while authenticated as feature timelime
    Given I am authenticated with the "ROLE_SYSTEM_FEATURE_TIMELINE" role
    And the GitHub account "foo" has the installation "0000"
    And the token of the GitHub installation "0000" is "1234"
    And I have a flow "00000000-0000-0000-0000-000000000000" with a GitHub repository "bar" owned by "foo"
    When I request the GitHub installation token for the flow "00000000-0000-0000-0000-000000000000"
    Then I should receive the installation token "1234"

  Scenario: I can't get the token if I'm not authenticated
    Given the GitHub account "foo" has the installation "0000"
    And the token of the GitHub installation "0000" is "1234"
    And I have a flow "00000000-0000-0000-0000-000000000000" with a GitHub repository "bar" owned by "foo"
    When I request the GitHub installation token for the flow "00000000-0000-0000-0000-000000000000"
    Then I should be told I need be authenticated

  Scenario: I can't get the token if I'm not authenticated as feature timelime
    Given I am authenticated as "samuel"
    And the GitHub account "foo" has the installation "0000"
    And the token of the GitHub installation "0000" is "1234"
    And I have a flow "00000000-0000-0000-0000-000000000000" with a GitHub repository "bar" owned by "foo"
    When I request the GitHub installation token for the flow "00000000-0000-0000-0000-000000000000"
    Then I should not be allowed to see the installation token
