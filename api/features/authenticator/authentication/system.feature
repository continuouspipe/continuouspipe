Feature:
  In order to have access to users credentials
  As an internal component
  I need to be able to be connected as system and access data of all users

  Scenario: If the API key do not exists, the access is refused
    Given there is a user "samuel"
    When I request the details of user "samuel" with the api key "1234"
    Then I should be told that I am not identified

  Scenario: I can authenticate with an API key as system and access to any user
    Given there is the system api key "1234"
    And there is a user "samuel"
    When I request the details of user "samuel" with the api key "1234"
    Then I should receive the details

  Scenario: As system, I can have access to any bucket
    Given there is the system api key "0987654321"
    And there is a bucket "00000000-0000-0000-0000-000000000000"
    When I ask the list of the GitHub tokens in the bucket "00000000-0000-0000-0000-000000000000" with the API key "0987654321"
    Then I should receive a list

  Scenario: As system, I can have access to any team details
    Given there is the system api key "0987654321"
    And there is a team "foo"
    And the user "samuel" is user of the team "foo"
    When I request the details of team "foo" with the API key "0987654321"
    Then I should see the team details

  Scenario: As system, I can get the user behind an API key
    Given there is the system api key "0987654321"
    And there is a user "samuel"
    And the user "samuel" have the API key "1234567890"
    When I request the user behind the API key "1234567890" with the API key "0987654321"
    Then I should see the user "samuel" for this API key

  Scenario: As a user, I'm forbidden to lookup API keys
    Given I am authenticated as user "samuel"
    And there is a user "foo"
    And the user "foo" have the API key "1234567890"
    When I request the user behind the API key "1234567890" with the API key "0987654321"
    Then I should be told that I don't have the authorization to access this API key

  Scenario: It returns a 404 if the API key is not found
    Given there is the system api key "0987654321"
    When I request the user behind the API key "1234567890" with the API key "0987654321"
    Then I should be told that the API key is not found
