Feature:
  In order to allow external authentication
  As a user
  I want to be able to manage API keys through the API

  Scenario: Create an API key
    Given I am authenticated as user "samuel"
    When I create an API key described "Some usage" for the user "samuel"
    Then the API key should have been created

  Scenario: List my API keys
    Given I am authenticated as user "samuel"
    And the user "samuel" have the API key "1234567890"
    When I request the list of API keys of the user "samuel"
    Then I should see the API key "1234567890"

  Scenario: I can't list other's API keys
    Given there is a user "foo"
    And I am authenticated as user "samuel"
    When I request the list of API keys of the user "foo"
    Then I should be told that I don't have the authorization to access this user

  Scenario: I can't create API keys for others
    Given there is a user "foo"
    And I am authenticated as user "samuel"
    When I create an API key described "Some usage" for the user "foo"
    Then I should be told that I don't have the authorization to access this user
