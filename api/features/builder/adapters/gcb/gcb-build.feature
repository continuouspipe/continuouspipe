Feature:
  In order to build Docker images
  As a developer
  I should be able to use GCB to build Docker images

  Background:
    Given I am authenticated
    And there is the bucket "00000000-0000-0000-0000-000000000000"
    And the bucket "00000000-0000-0000-0000-000000000000" contains the following github tokens:
      | identifier | token |
      | sroze      | 12345 |

  Scenario: Send manifest to GCB
    Given the bucket "00000000-0000-0000-0000-000000000000" contains the following docker registry credentials:
      | username | password | serverAddress | email                 |
      | samuel   | samuel   | docker.io     | samuel.roze@gmail.com |
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
        "type": "gcb"
      }
    }
    """
    Then the manifest file should be sent in an archive to to Google Cloud Storage
    And the archive details should be sent to Google Cloud Builder

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
        "type": "gcb"
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
      "credentialsBucket": "00000000-0000-0000-0000-000000000000",
      "engine": {
        "type": "gcb"
      }
    }
    """
    Then the request should be refused with a 400 status code
    And the response should contain the following JSON:
    """
    {
       "message": "Docker Registry credentials for the image \"sroze/php-example\" not found"
    }
    """
