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
      "notification": {
        "http": {
          "address": "https://example.com"
        }
      },
      "credentialsBucket": "00000000-0000-0000-0000-000000000000",
      "engine": {
        "type": "gcb"
      }
    }
    """
    Then a log containing "Re-using already built Docker image <code>sroze/php-example:continuous</code>" should be created
    And the archive details should not be sent to Google Cloud Builder
    And the build should be successful

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
      "notification": {
        "http": {
          "address": "https://example.com"
        }
      },
      "credentialsBucket": "00000000-0000-0000-0000-000000000000",
      "engine": {
        "type": "gcb"
      }
    }
    """
    Then a log containing "Re-using already built Docker image <code>sroze/php-example:continuous</code>" should be created
    And a log containing "Re-using already built Docker image <code>sroze/php-example:v2</code>" should be created
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
      "notification": {
        "http": {
          "address": "https://example.com"
        }
      },
      "credentialsBucket": "00000000-0000-0000-0000-000000000000",
      "engine": {
        "type": "gcb"
      }
    }
    """
    Then a log containing "Re-using already built Docker image <code>sroze/php-example:continuous</code>" should not be found
    And a log containing "Re-using already built Docker image <code>sroze/php-example:v2</code>" should not be found

  Scenario: Force to not re-use existing built image
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
            "tag": "continuous",
            "reuse": false
          },
          "repository": {
            "address": "fixtures://php-example",
            "branch": "747850e8c821a443a7b5cee28a48581069049739"
          }
        }
      ],
      "notification": {
        "http": {
          "address": "https://example.com"
        }
      },
      "credentialsBucket": "00000000-0000-0000-0000-000000000000",
      "engine": {
        "type": "gcb"
      }
    }
    """
    Then a log containing "Re-using already built Docker image <code>sroze/php-example:continuous</code>" should not be found
    And the archive details should be sent to Google Cloud Builder
