Feature:
  In order to build Docker images
  As a developer
  I should be able to call the builder API to build Docker images

  Background:
    Given I am authenticated
    And there is the bucket "00000000-0000-0000-0000-000000000000"
    And the bucket "00000000-0000-0000-0000-000000000000" contains the following github tokens:
      | identifier | token |
      | sroze      | 12345 |

  Scenario: Successful build
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
        "type": "docker"
      }
    }
    """
    Then the build should be successful
    And the image "sroze/php-example:continuous" should be built
    And the image "sroze/php-example:continuous" should be pushed

  Scenario: Successful build using the previous non-steps API
    Given the bucket "00000000-0000-0000-0000-000000000000" contains the following docker registry credentials:
      | username | password | serverAddress | email                 |
      | samuel   | samuel   | docker.io     | samuel.roze@gmail.com |
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
        "type": "docker"
      }
    }
    """
    Then the build should be successful
    And the image "sroze/php-example:continuous" should be built
    And the image "sroze/php-example:continuous" should be pushed

  Scenario: In case of failure, it will re-start the step
    Given the bucket "00000000-0000-0000-0000-000000000000" contains the following docker registry credentials:
      | username | password | serverAddress | email                 |
      | samuel   | samuel   | docker.io     | samuel.roze@gmail.com |
    And there is a build "00000000-0000-0000-0000-000000000000" with the following request:
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
    And the build "00000000-0000-0000-0000-000000000000" was started
    And the build "00000000-0000-0000-0000-000000000000" step #0 was started
    When I start the build "00000000-0000-0000-0000-000000000000"
    Then the image "sroze/php-example:continuous" should be built
    And the image "sroze/php-example:continuous" should be pushed
