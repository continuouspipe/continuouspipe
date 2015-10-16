Feature:
  In order to have teams automatically configured
  As a user
  My credentials are by default copied into the team

  Background:
    Given I am authenticated as user "samuel"

  @smoke
  Scenario: If a user creates a team, his credentials are automatically copy the GitHub tokens into the team's bucket
    Given the bucket of the user "samuel" is the "11111111-0000-0000-0000-000000000000"
    And I have the following GitHub tokens in the bucket "11111111-0000-0000-0000-000000000000":
    | login | accessToken |
    | sroze | e72e16c7e42f292c6912e7710c838347ae178b4a |
    When I create a team "foo"
    Then the bucket of the team "foo" should contain the GitHub token "e72e16c7e42f292c6912e7710c838347ae178b4a"
