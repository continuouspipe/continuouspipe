Feature:
  In order to be able to build images from a non-specific source
  As a user
  I want to be able to send an archive URL for the builder to download it

  Background:
    Given I am authenticated
    Given there is the bucket "00000000-0000-0000-0000-000000000000"
    And the bucket "00000000-0000-0000-0000-000000000000" contains the following docker registry credentials:
      | username | password | serverAddress | email                 |
      | samuel   | samuel   | docker.io     | samuel.roze@gmail.com |

  Scenario: It downloads the archive from an URL
    Given the URL "https://bitbucket.org/sroze/testing-stuff/get/001823eef762ac0325b79293f8530feafec3fdcc.tar.gz" will return the archive "001823eef762ac0325b79293f8530feafec3fdcc.tar.gz"
    When I send the following build request:
    """
    {
      "image": {
        "name": "sroze/php-example",
        "tag": "master"
      },
      "archive": {
        "url": "https://bitbucket.org/sroze/testing-stuff/get/001823eef762ac0325b79293f8530feafec3fdcc.tar.gz"
      },
      "credentialsBucket": "00000000-0000-0000-0000-000000000000"
    }
    """
    Then the build should be successful
    And the archive should have been downloaded from the URL "https://bitbucket.org/sroze/testing-stuff/get/001823eef762ac0325b79293f8530feafec3fdcc.tar.gz"
    And the archive should contain the file "README.md"

  Scenario: It uses custom HTTP headers
    Given the URL "https://bitbucket.org/sroze/testing-stuff/get/001823eef762ac0325b79293f8530feafec3fdcc.tar.gz" will return the archive "001823eef762ac0325b79293f8530feafec3fdcc.tar.gz"
    When I send the following build request:
    """
    {
      "image": {
        "name": "sroze/php-example",
        "tag": "master"
      },
      "archive": {
        "url": "https://bitbucket.org/sroze/testing-stuff/get/001823eef762ac0325b79293f8530feafec3fdcc.tar.gz",
        "headers": {
          "Authorization": "token FOO-BAR",
          "X-SpanId": "1234"
        }
      },
      "credentialsBucket": "00000000-0000-0000-0000-000000000000"
    }
    """
    Then the build should be successful
    And the archive should have been downloaded from the URL "https://bitbucket.org/sroze/testing-stuff/get/001823eef762ac0325b79293f8530feafec3fdcc.tar.gz" with the following headers:
      | name          | value         |
      | Authorization | token FOO-BAR |
      | X-SpanId      | 1234          |
