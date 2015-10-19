Feature:
  In order to have information about the user
  As an API client
  I need to be able to request user details

  Scenario: I can request my own user details
    Given I am authenticated as user "samuel"
    When I request the details of user "samuel"
    Then I should receive the details

  Scenario: I cannot request details of other users
    Given I am authenticated as user "samuel"
    And there is a user "foo"
    When I request the details of user "foo"
    Then I should be told that I don't have the authorization to access this user
