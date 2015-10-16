Feature:
  In order to give the system access to the code repositories
  As a user
  I need to be able to have manage the GitHub tokens in a bucket

  Background:
    Given I am authenticated as user "samuel"
    And the user "samuel" have access to the bucket "00000000-0000-0000-0000-000000000000"

  Scenario: I can create new GitHub tokens
    When I create a GitHub token with the following configuration in the bucket "00000000-0000-0000-0000-000000000000":
      | login | accessToken                              |
      | sroze | e72e16c7e42f292c6912e7710c838347ae178b4a |
    Then the new credentials should have been saved successfully

  Scenario: The credentials creation fails if some fields are missing
    When I create a GitHub token with the following configuration in the bucket "00000000-0000-0000-0000-000000000000":
      | login |
      | sroze |
    Then I should receive a bad request error

  Scenario: I can list the Docker Registry credentials of a bucket
    Given I have the following GitHub tokens in the bucket "00000000-0000-0000-0000-000000000000":
      | login | accessToken                              |
      | sroze | e72e16c7e42f292c6912e7710c838347ae178b4a |
    When I ask the list of the GitHub tokens in the bucket "00000000-0000-0000-0000-000000000000"
    Then I should receive a list
    And the list should contain the access token "e72e16c7e42f292c6912e7710c838347ae178b4a"
