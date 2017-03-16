Feature:
  In order to get access to ContinuousPipe
  As a user
  I should be able to login with GitHub and my account should be ready to use

  Scenario: If a user is not in the white list, he CAN authenticate himself (now)
    Given The user "samuel" is not in the white list
    When the user "samuel" try to authenticate himself with GitHub
    Then the authentication should be successful

  Scenario: If a user is in the white list, he can authenticate himself
    Given The user "samuel" is in the white list
    When the user "samuel" try to authenticate himself with GitHub
    Then the authentication should be successful

  @smoke
  Scenario: I am a new user, my account is created my bucket created and filled
    Given The user "sroze" is in the white list
    When a login with GitHub as "sroze" with the token "1234"
    Then the authentication should be successful
    And the user "sroze" should exists
    And the bucket of the user "sroze" should contain the GitHub token "1234"

  Scenario: The GitHub account is linked when login-in with GitHub
    Given The user "sroze" is in the white list
    When a login with GitHub as "sroze" with the token "1234"
    Then the authentication should be successful
    And the user "sroze" should exists
    And the user "sroze" should be linked to a GitHub account with username "sroze"

  Scenario: I am a new user, a billing account should be created
    Given The user "sroze" is in the white list
    When a login with GitHub as "sroze" with the token "1234"
    Then the authentication should be successful
    And the user "sroze" should exists
    And the user "sroze" should have a billing account

  @smoke
  Scenario: A user can authenticate himself using an API key
    Given there is a user "samuel"
    And the user "samuel" have the API key "123456"
    When I request the details of user "samuel" with the api key "123456"
    Then I should receive the details

  Scenario: I can't access other resources
    Given there is a user "samuel"
    And there is a user "tony"
    And the user "samuel" have the API key "123456"
    When I request the details of user "tony" with the api key "123456"
    Then I should be told that I don't have the authorization to access this user

  Scenario: Can't authenticate with wrong API keys
    Given there is a user "samuel"
    And the user "samuel" have the API key "123456"
    When I request the details of user "samuel" with the api key "0987654"
    Then I should be told that I am not identified
