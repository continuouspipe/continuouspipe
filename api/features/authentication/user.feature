Feature:
  In order to get access to ContinuousPipe
  As a user
  I should be able to login with GitHub and my account should be ready to use

  Scenario: If a user is not in the white list, he can't authenticate himself
    Given The user "samuel" is not in the white list
    When the user "samuel" try to authenticate himself with GitHub
    Then the authentication should be failed

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
