Feature:
  In order to easily get started with a remote environment
  As a user
  I want to be able to create a virtual environment client

  Background:
    Given the team "continuous-pipe" exists
    And there is a user "samuel"
    And the user "samuel" is "user" of the team "continuous-pipe"
    And I have a flow with UUID "00000000-0000-0000-0000-000000000000" in the team "continuous-pipe"
    And I am authenticated as "samuel"

  @smoke
  Scenario: Create an environment for a flow
    When I create a development environment named "sroze's environment" for the flow "00000000-0000-0000-0000-000000000000"
    And I request the list of the development environments of the flow "00000000-0000-0000-0000-000000000000"
    Then I should see the development environment "sroze's environment"

  @smoke
  Scenario: I can create an initialization token that will be used by the client
    Given the user "samuel" have a development environment "00000000-0000-0000-0000-000000000000" for the flow "00000000-0000-0000-0000-000000000000"
    And the created API key for the user "samuel" will have the key "API-KEY-1234"
    When I create an initialization token for the development environment "00000000-0000-0000-0000-000000000000" of the flow "00000000-0000-0000-0000-000000000000" with the following parameters:
    """
    {
      "git_branch": "cpdev/sroze"
    }
    """
    Then I receive a token that contains the following base64 decoded and comma separated values:
      # | api-key    | environment-uuid                     | flow-uuid                                   |  username         | git-branch  |
      | API-KEY-1234 | 00000000-0000-0000-0000-000000000000 | 00000000-0000-0000-0000-000000000000        |  samuel           | cpdev/sroze |

  Scenario: Delete the development environment
    Given the user "samuel" have a development environment "00000000-0000-0000-0000-000000000000" for the flow "00000000-0000-0000-0000-000000000000"
    When I delete the development environment "00000000-0000-0000-0000-000000000000" of the flow "00000000-0000-0000-0000-000000000000"
    Then the development environment should be successfully deleted
