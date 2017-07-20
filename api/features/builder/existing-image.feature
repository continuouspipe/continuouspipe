Feature:
  In order to save time
  As a developer
  I should not build an image that already exists on the registry

  Background:
    Given I am authenticated
    And there is the bucket "00000000-0000-0000-0000-000000000000"
    And the bucket "00000000-0000-0000-0000-000000000000" contains the following github tokens:
      | identifier | token |
      | sroze      | 12345 |

  Scenario: Reuse existing image
    Given the bucket "00000000-0000-0000-0000-000000000000" contains the following docker registry credentials:
      | username | password | serverAddress | email                 |
      | samuel   | samuel   | docker.io     | samuel.roze@gmail.com |
    And the image "sroze/php-example:continuous" exists in the docker registry
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
    Then a log containing "Re-using pre-built Docker image sroze/php-example:continuous" should be created
    And the archive details should not be sent to Google Cloud Builder

  Scenario: Reuse existing images when multiple steps
    Given the bucket "00000000-0000-0000-0000-000000000000" contains the following docker registry credentials:
      | username | password | serverAddress | email                 |
      | samuel   | samuel   | docker.io     | samuel.roze@gmail.com |
    And the image "sroze/php-example:continuous" exists in the docker registry
    And the image "sroze/php-example:v2" exists in the docker registry
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
        },
        {
          "image": {
            "name": "sroze/php-example",
            "tag": "v2"
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
    Then a log containing "Re-using pre-built Docker image sroze/php-example:continuous" should be created
    And a log containing "Re-using pre-built Docker image sroze/php-example:v2" should be created
    And the archive details should not be sent to Google Cloud Builder

  Scenario: Don't reuse existing images when any are missing
    Given the bucket "00000000-0000-0000-0000-000000000000" contains the following docker registry credentials:
      | username | password | serverAddress | email                 |
      | samuel   | samuel   | docker.io     | samuel.roze@gmail.com |
    And the image "sroze/php-example:continuous" exists in the docker registry
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
        },
        {
          "image": {
            "name": "sroze/php-example",
            "tag": "v2"
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
    Then a log containing "Re-using pre-built Docker image sroze/php-example:continuous" should not be found
    And a log containing "Re-using pre-built Docker image sroze/php-example:v2" should not be found


