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
      "credentialsBucket": "00000000-0000-0000-0000-000000000000"
    }
    """
    Then the build should be successful
    And the notification should be sent


  Scenario: It notify if asked
    Given the "api_retry_count" parameter is set to "5"
    And the notification will fail the first 5 times
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
      "credentialsBucket": "00000000-0000-0000-0000-000000000000"
    }
    """
    Then the build should be successful
    And the notification should be sent
    And a log containing "Notification failed." should not be found

  Scenario: Limit the number of retried API calls
    Given the "api_retry_count" parameter is set to "5"
    And the notification will fail the first 6 times
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
      "credentialsBucket": "00000000-0000-0000-0000-000000000000"
    }
    """
    Then the build should be failed
    And a log containing "Notification failed." should be created once

  Scenario: Record the number of failed API calls in StatsD
    Given the "api_retry_count" parameter is set to "5"
    And the notification will fail the first 3 times
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
      "credentialsBucket": "00000000-0000-0000-0000-000000000000"
    }
    """
    Then the build should be successful
    And I should see the metrics published as below:
      | metric                             | value |
      | builder.outgoing.notifier.success  | 2     |
      | builder.outgoing.notifier.failure  | 3     |