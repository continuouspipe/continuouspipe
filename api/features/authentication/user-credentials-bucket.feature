Feature:
  In order to get access from the user's account
  As a user
  My GitHub token credentials are automatically created when I login

  @smoke
  Scenario: I am a new user, my account is created my bucket created and filled
    Given The user "sroze" is in the white list
    When a login with GitHub as "sroze" with the token "1234"
    Then the authentication should be successful
    And the user "sroze" should exists
    And the bucket of the user "sroze" should contain the GitHub token "1234"
