Feature:
  In order to improve the performance of the GitHub integration
  As a system
  I want to cache the API calls to GitHub

  Scenario: GitHub access tokens are cached
    Given the cache is clean
    And the GitHub account "foo" have the installation "5678"
    And the token of the GitHub installation "5678" is "1234"
    When I look up the token for installation "5678" 5 times
    Then GitHub access token API should have been called once

  Scenario: GitHub repositories are cached
    Given the cache is clean
    And the GitHub account "sroze" have the installation "5678"
    And the GitHub repository "bar" exists
    When I look up the installation for repository "bar" 5 times
    Then GitHub repository API should have been called once

  Scenario: GitHub access token cache is cleared when installation is deleted
    Given the cache is clean
    And the GitHub account "foo" have the installation "5678"
    And the token of the GitHub installation "5678" is "1234"
    And the token for installation "5678" is retrieved once
    When GitHub installation "5678" is deleted
    And I look up the token for installation "5678" 5 times
    Then GitHub access token API should have been called twice

  Scenario: GitHub repository cache is cleared when installation is deleted
    Given the cache is clean
    And the GitHub account "sroze" have the installation "5678"
    And the GitHub repository "bar" exists
    And the installation for repository "bar" is retrieved once
    When GitHub installation "5678" is deleted
    And I look up the installation for repository "bar" 5 times
    Then GitHub repository API should have been called twice

  Scenario: GitHub repository cache is cleared when a repository is added
    Given the cache is clean
    And the GitHub account "sroze" have the installation "5678"
    And the GitHub repository "bar" exists
    And the installation for repository "bar" is retrieved once
    When a GitHub repository is added to the installation "5678"
    And I look up the installation for repository "bar" 5 times
    Then GitHub repository API should have been called twice

  Scenario: GitHub repository cache is cleared when a repository is removed
    Given the cache is clean
    And the GitHub account "sroze" have the installation "5678"
    And the GitHub repository "bar" exists
    And the installation for repository "bar" is retrieved once
    When a GitHub repository is removed from the installation "5678"
    And I look up the installation for repository "bar" 5 times
    Then GitHub repository API should have been called twice
