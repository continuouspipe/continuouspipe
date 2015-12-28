Feature:
  In order to run my application
  As a developer
  I want to be able to control the image that will be run

  Background:
    Given I am authenticated
    And I am building a deployment request
    And the target environment name is "my-environment"
    And the target cluster identifier is "my-cluster"
    And the credentials bucket is "00000000-0000-0000-0000-000000000000"
    And there is a cluster in the bucket "00000000-0000-0000-0000-000000000000" with the following configuration:
      | identifier | type       | address         | version | username | password |
      | my-cluster | kubernetes | https://1.2.3.4 | v1      | username | password |

  Scenario: I can uses an image name including the registry name
    Given the specification come from the template "simple-private-app"
    And the pods of the replication controller "app" will be running after creation
    When I send the built deployment request
    Then the image name of the deployed component "app" should be "docker.io/foo/bar"
    And the image tag of the deployed component "app" should be "master"

  Scenario: I can override the default command
    Given the specification come from the template "overwrite-command"
    And the pods of the replication controller "mysql" will be running after creation
    When I send the built deployment request
    Then the component "mysql" should be deployed with the command "echo hello"
