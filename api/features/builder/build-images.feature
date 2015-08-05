Feature:
  In order to build Docker images
  As a developer
  I should be able to call the builder API to build Docker images

  Scenario:
    Given I am authenticated
    When I send the following build request:
    """
    {
      "image": {
        "name": "sroze/php-example",
        "tag": "continuous"
      },
      "repository": {
        "address": "https://github.com/sroze/docker-php-example",
        "branch": "747850e8c821a443a7b5cee28a48581069049739"
      }
    }
    """
    Then the image "sroze/php-example:continuous" should be built
