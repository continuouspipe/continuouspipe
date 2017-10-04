Feature:
  In order to have insights about potential failures
  As a user
  I want to be told if the image can't be built

  Background:
    Given I am authenticated
    And there is the bucket "00000000-0000-0000-0000-000000000000"
    And the bucket "00000000-0000-0000-0000-000000000000" contains the following github tokens:
      | identifier | token |
      | sroze      | 12345 |

  Scenario: The build should fail without Docker Registry credentials
    When I send the following build request:
    """
    {
      "steps": [
        {
          "image": {
            "name": "sroze/php-example",
            "tag": "continuous"
          },
          "repository": {
            "address": "fixtures://php-example",
            "branch": "747850e8c821a443a7b5cee28a48581069049739"
          }
        }
      ],
      "credentialsBucket": "00000000-0000-0000-0000-000000000000",
      "engine": {
        "type": "docker"
      }
    }
    """
    Then the build should be errored

  Scenario: It returns a 400 and an error message with the credentials are not found
    When I send the following build request:
    """
    {
      "image": {
        "name": "sroze/php-example",
        "tag": "continuous"
      },
      "repository": {
        "address": "fixtures://php-example",
        "branch": "747850e8c821a443a7b5cee28a48581069049739"
      },
      "credentialsBucket": "00000000-0000-0000-0000-000000000000"
    }
    """
    Then the request should be refused with a 400 status code
    And the response should contain the following JSON:
    """
    {
       "message": "Docker Registry credentials for the image \"sroze/php-example\" not found"
    }
    """
