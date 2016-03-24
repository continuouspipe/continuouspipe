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

  Scenario:
    When I send a build request for the fixture repository "private-dependencies" with the following environment:
      | name               | value |
      | MY_PRIVATE_ENVIRON | foo   |
    Then the build should be successful
    And the image "my/image:master" should be built
    And the command "MY_PRIVATE_ENVIRON=foo sh -c './private-check.sh'" should be ran on image "my/image:master"
    And a container should be committed with the image name "my/image:master"
    And the image "my/image:master" should be pushed
    And the command of the image "my/image:master" should be "/app/my-cmd.sh"

  @integration
  Scenario:
    When I send a build request for the fixture repository "build-args" with the following environment:
      | name          | value |
      | MY_CUSTOM_ENV | foo   |
    Then the build should be successful
    And the file "/result" in the image "my/image:master" should contain "foo"
