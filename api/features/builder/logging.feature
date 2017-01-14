Feature:
  In order to see how my build is going
  As a user
  I should be able to see if something wrong is happening as well as if everything is alright

  Background:
    Given I am authenticated
    And there is the bucket "00000000-0000-0000-0000-000000000000"
    And the bucket "00000000-0000-0000-0000-000000000000" contains the following docker registry credentials:
      | username | password | serverAddress | email                 |
      | samuel   | samuel   | docker.io     | samuel.roze@gmail.com |

  Scenario: The reason of a failing build is displayed
    Given the Docker build will fail because of "This specific thing went wrong"
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
            "branch": "747850e8c821a443a7b5cee28a48581069049739",
            "token": "foo/bar"
          }
        }
      ],
      "credentialsBucket": "00000000-0000-0000-0000-000000000000"
    }
    """
    Then a log containing "This specific thing went wrong" should be created
