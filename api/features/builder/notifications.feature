Feature:
  In order to continue any process relying on this images to be built
  As a system
  I want the builder to send a notification with the build status

  Background:
    Given I am authenticated
    And there is the bucket "00000000-0000-0000-0000-000000000000"
    And the bucket "00000000-0000-0000-0000-000000000000" contains the following docker registry credentials:
      | username | password | serverAddress | email                 |
      | samuel   | samuel   | docker.io     | samuel.roze@gmail.com |
    And the bucket "00000000-0000-0000-0000-000000000000" contains the following github tokens:
      | identifier | token |
      | sroze      | 12345 |

  Scenario: It notify if asked
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
      "notification": {
        "http": {
          "address": "https://example.com"
        }
      },
      "credentialsBucket": "00000000-0000-0000-0000-000000000000"
    }
    """
    Then the build should be successful
    And the notification should be sent


  Scenario: It notify if asked
    Given the notification will fail the first 2 times
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
      "notification": {
        "http": {
          "address": "https://example.com"
        }
      },
      "credentialsBucket": "00000000-0000-0000-0000-000000000000"
    }
    """
    Then the build should be successful
    And the notification should be sent
