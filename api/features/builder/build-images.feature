Feature:
  In order to build Docker images
  As a developer
  I should be able to call the builder API to build Docker images

  Background:
    Given I am authenticated

  Scenario:
    Given I have docker registry credentials
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
      }
    }
    """
    Then the build should be successful
    And the image "sroze/php-example:continuous" should be built
    And the image "sroze/php-example:continuous" should be pushed

  Scenario: The build should fail without Docker Registry credentials
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
      }
    }
    """
    Then the build should be errored
    And the image "sroze/php-example:continuous" should be built
