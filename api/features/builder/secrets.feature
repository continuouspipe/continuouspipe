Feature:
  In order to build applications with private dependencies
  As a developer
  I want to be able to run build with environment variables that won't be in the final image

  Background:
    Given I am authenticated
    And there is the bucket "00000000-0000-0000-0000-000000000000"
    And the bucket "00000000-0000-0000-0000-000000000000" contains the following docker registry credentials:
      | username | password | serverAddress | email                 |
      | samuel   | samuel   | docker.io     | samuel.roze@gmail.com |

  @integration
  Scenario:
    When I send a build request for the fixture repository "build-args" with the following environment:
      | name          | value |
      | MY_CUSTOM_ENV | foo   |
    Then the build should be successful
    And the file "/result" in the image "my/image:master" should contain "foo"
