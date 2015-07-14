Feature:
  In order to have to run real integration tests
  As developer
  I should be able to have a running environment for a pull request

  Scenario:
    Given I have a Dockerfile at the root of my project
    When I create a pull request
    Then the images should be built
    And the matching environment should be deployed


