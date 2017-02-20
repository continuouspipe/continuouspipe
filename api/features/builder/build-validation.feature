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
    And the bucket "00000000-0000-0000-0000-000000000000" contains the following docker registry credentials:
      | username | password | serverAddress | email                 |
      | samuel   | samuel   | docker.io     | samuel.roze@gmail.com |

  Scenario: The default Dockerfile exists
    When I send the following build request:
    """
    {
      "steps": [
        {
          "image": {
            "name": "sroze/php-example",
            "tag": "continuous"
          },
          "context": {
            "docker_file_path": "",
            "repository_sub_directory": "."
          },
          "repository": {
            "address": "fixtures://php-example",
            "branch": "747850e8c821a443a7b5cee28a48581069049739"
          }
        }
      ],
      "credentialsBucket": "00000000-0000-0000-0000-000000000000"
    }
    """
    Then the build should be successful
    And the image "sroze/php-example:continuous" should be built

  Scenario: The specified Dockerfile exists
    When I send the following build request:
    """
    {
      "steps": [
        {
          "image": {
            "name": "sroze/php-example",
            "tag": "continuous"
          },
          "context": {
            "docker_file_path": "Dockerfile",
            "repository_sub_directory": "."
          },
          "repository": {
            "address": "fixtures://php-example",
            "branch": "747850e8c821a443a7b5cee28a48581069049739"
          }
        }
      ],
      "credentialsBucket": "00000000-0000-0000-0000-000000000000"
    }
    """
    Then the build should be successful
    And the image "sroze/php-example:continuous" should be built

  Scenario: The Dockerfile do not exists
    When I send the following build request:
    """
    {
      "steps": [
        {
          "image": {
            "name": "sroze/php-example",
            "tag": "continuous"
          },
          "context": {
            "docker_file_path": "Somefile",
            "repository_sub_directory": "."
          },
          "repository": {
            "address": "fixtures://php-example",
            "branch": "747850e8c821a443a7b5cee28a48581069049739"
          }
        }
      ],
      "credentialsBucket": "00000000-0000-0000-0000-000000000000"
    }
    """
    Then the build should be failed
    And a log containing "The build configuration file `Somefile` was not found" should be created

  Scenario: The Dockerfile do not exists
    When I send the following build request:
    """
    {
      "steps": [
        {
          "image": {
            "name": "sroze/php-example",
            "tag": "continuous"
          },
          "context": {
            "docker_file_path": "Dockerfile",
            "repository_sub_directory": "./sub-directory"
          },
          "repository": {
            "address": "fixtures://php-example",
            "branch": "747850e8c821a443a7b5cee28a48581069049739"
          }
        }
      ],
      "credentialsBucket": "00000000-0000-0000-0000-000000000000"
    }
    """
    Then the build should be failed
    And a log containing "The build configuration file `Dockerfile` was not found (in `./sub-directory`)" should be created

  Scenario: The Dockerfile do not exists
    When I send the following build request:
    """
    {
      "steps": [
        {
          "image": {
            "name": "sroze/php-example",
            "tag": "continuous"
          },
          "context": {
            "docker_file_path": "",
            "repository_sub_directory": "./sub-directory"
          },
          "repository": {
            "address": "fixtures://php-example",
            "branch": "747850e8c821a443a7b5cee28a48581069049739"
          }
        }
      ],
      "credentialsBucket": "00000000-0000-0000-0000-000000000000"
    }
    """
    Then the build should be failed
    And a log containing "The build configuration file `Dockerfile` was not found (in `./sub-directory`)" should be created

  Scenario: The default Dockerfile do not exists
    When I send the following build request:
    """
    {
      "steps": [
        {
          "image": {
            "name": "sroze/php-example",
            "tag": "continuous"
          },
          "context": {
            "docker_file_path": "",
            "repository_sub_directory": "."
          },
          "repository": {
            "address": "fixtures://another-name",
            "branch": "747850e8c821a443a7b5cee28a48581069049739"
          }
        }
      ],
      "credentialsBucket": "00000000-0000-0000-0000-000000000000"
    }
    """
    Then the build should be failed
    And a log containing "The build configuration file `Dockerfile` was not found" should be created

  Scenario: The custom Dockerfile exists
    When I send the following build request:
    """
    {
      "steps": [
        {
          "image": {
            "name": "sroze/php-example",
            "tag": "continuous"
          },
          "context": {
            "docker_file_path": "Buildfile",
            "repository_sub_directory": "./build"
          },
          "repository": {
            "address": "fixtures://another-name",
            "branch": "747850e8c821a443a7b5cee28a48581069049739"
          }
        }
      ],
      "credentialsBucket": "00000000-0000-0000-0000-000000000000"
    }
    """
    Then the build should be successful
    And the image "sroze/php-example:continuous" should be built

  Scenario: The custom Dockerfile exists in a sub-directory
    When I send the following build request:
    """
    {
      "steps": [
        {
          "image": {
            "name": "sroze/php-example",
            "tag": "continuous"
          },
          "context": {
            "docker_file_path": "",
            "repository_sub_directory": "./some/thing/"
          },
          "repository": {
            "address": "fixtures://dockerfile-in-sub-directory",
            "branch": "747850e8c821a443a7b5cee28a48581069049739"
          }
        }
      ],
      "credentialsBucket": "00000000-0000-0000-0000-000000000000"
    }
    """
    Then the build should be successful
    And the image "sroze/php-example:continuous" should be built
