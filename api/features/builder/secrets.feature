Feature:
  In order to build applications with private dependencies
  As a developer
  I want to be able to run build with environment variables that won't be in the final image

  Background:
    Given I am authenticated
    And there is the bucket "00000000-0000-0000-0000-000000000000"
    And the bucket "00000000-0000-0000-0000-000000000000" contains the Docker Registry credentials
    And the bucket "00000000-0000-0000-0000-000000000000" contains the following github tokens:
      | identifier | token |
      | sroze      | 12345 |

  @integration
  Scenario: It injects build arguments
    When I send the following build request:
    """
    {
      "image": {
        "name": "docker.io/continuouspipepublicrobot/test",
        "tag": "build-args"
      },
      "repository": {
        "address": "fixtures://build-args",
        "branch": "master"
      },
      "credentialsBucket": "00000000-0000-0000-0000-000000000000",
      "environment": {
        "MY_CUSTOM_ENV": "foo"
      }
    }
    """
    Then the build should be successful
    And the file "/result" in the image "docker.io/continuouspipepublicrobot/test:build-args" should contain "foo"
