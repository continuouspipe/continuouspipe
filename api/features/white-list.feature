Feature:
  In order to limit access to ContinuousPipe to a small range of users
  I want to restrict login access to some white-listed users

  Scenario: If a user is not in the white list, he can't authenticate himself
    Given The user "samuel" is not in the white list
    When the user "samuel" try to authenticate himself with GitHub
    Then the authentication should be failed

  Scenario: If a user is in the white list, he can authenticate himself
    Given The user "samuel" is in the white list
    When the user "samuel" try to authenticate himself with GitHub
    Then the authentication should be successful
