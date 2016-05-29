Feature:
  In order to have access to users credentials
  As an internal component
  I need to be able to be connected as system and access data of all users

  Scenario: If the API key do not exists, the access is refused
    Given there is a user "samuel"
    When I request the details of user "samuel" with the api key "1234"
    Then I should be told that I am not identified

  Scenario: I can authenticate with an API key as system and access to any user
    Given there is the api key "1234"
    And there is a user "samuel"
    When I request the details of user "samuel" with the api key "1234"
    Then I should receive the details

  Scenario: As system, I can have access to any bucket
    Given there is the api key "0987654321"
    And there is a bucket "00000000-0000-0000-0000-000000000000"
    When I ask the list of the GitHub tokens in the bucket "00000000-0000-0000-0000-000000000000" with the API key "0987654321"
    Then I should receive a list

  Scenario: As system, I can have access to any team details
    Given there is the api key "0987654321"
    And there is a team "foo"
    And the user "samuel" is user of the team "foo"
    When I request the details of team "foo" with the API key "0987654321"
    Then I should see the team details
