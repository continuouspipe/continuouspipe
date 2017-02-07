Feature:
  In order to start deployments
  As a user
  I want to be able to create a flow from a BitBucket repository

  Background:
    Given I am authenticated as "samuel"

  Scenario: I can list my repositories from a BitBucket account
    Given I have a BitBucket account "00000000-0000-0000-0000-000000000000" for the user "sroze"
    And the BitBucket user "sroze" have the following repositories:
      | uuid                                   | name |
      | {00000000-0000-0000-0000-000000000000} | foo  |
    When I request the "00000000-0000-0000-0000-000000000000" account's personal repositories
    Then I should see the following repositories:
      | type      | name |
      | bitbucket | foo  |

  Scenario: I can list my organisations
    Given I have a BitBucket account "00000000-0000-0000-0000-000000000000" for the user "sroze"
    And the BitBucket user "sroze" belong to the following teams:
      | username       |
      | continuouspipe |
    When I request the "00000000-0000-0000-0000-000000000000" account's organisations
    Then I should see the following organisations:
      | identifier     |
      | continuouspipe |

  Scenario: I can list the organisation's repository
    Given I have a BitBucket account "00000000-0000-0000-0000-000000000000" for the user "sroze"
    And the BitBucket team "continuouspipe" have the following repositories:
      | uuid                                   | name |
      | {00000000-0000-0000-0000-000000000000} | foo  |
    When I request the "00000000-0000-0000-0000-000000000000" account's repositories of the organisation "continuouspipe"
    Then I should see the following repositories:
      | type      | name |
      | bitbucket | foo  |

  Scenario: Create from a BitBucket repository
    Given the team "samuel" exists
    When I send a flow creation request for the team "samuel" with the BitBucket repository "{00000000-0000-0000-0000-000000000000}"
    Then the flow is successfully saved

  Scenario: I can list my repositories from a BitBucket account that have many pages
    Given I have a BitBucket account "00000000-0000-0000-0000-000000000000" for the user "sroze"
    And the BitBucket user "sroze" have the following repositories on the page 1 of 2:
      | uuid                                   | name |
      | {00000000-0000-0000-0000-000000000000} | foo  |
    And the BitBucket user "sroze" have the following repositories on the page 2 of 2:
      | uuid                                   | name |
      | {00000000-0000-0000-1111-000000000000} | bar  |
    When I request the "00000000-0000-0000-0000-000000000000" account's personal repositories
    Then I should see the following repositories:
      | type      | name |
      | bitbucket | foo  |
      | bitbucket | bar  |

  Scenario: I can list the organisation's repository on many pages
    Given I have a BitBucket account "00000000-0000-0000-0000-000000000000" for the user "sroze"
    And the BitBucket team "continuouspipe" have the following repositories on the page 1 of 2:
      | uuid                                   | name |
      | {00000000-0000-0000-0000-000000000000} | foo  |
    And the BitBucket team "continuouspipe" have the following repositories on the page 2 of 2:
      | uuid                                   | name |
      | {00000000-0000-0000-1111-000000000000} | bar  |
    When I request the "00000000-0000-0000-0000-000000000000" account's repositories of the organisation "continuouspipe"
    Then I should see the following repositories:
      | type      | name |
      | bitbucket | foo  |
      | bitbucket | bar  |
