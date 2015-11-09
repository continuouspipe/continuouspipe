Feature:
  In order to override images' commands
  As a developer
  I want to be able to override container command

  Background:
    Given I am authenticated
    And I am building a deployment request
    And the target environment name is "my-environment"
    And the target cluster identifier is "my-cluster"
    And the credentials bucket is "00000000-0000-0000-0000-000000000000"
    And there is a cluster in the bucket "00000000-0000-0000-0000-000000000000" with the following configuration:
      | identifier | type       | address         | version | username | password |
      | my-cluster | kubernetes | https://1.2.3.4 | v1      | username | password |

  Scenario:
    Given the specification come from the template "overwrite-command"
    When I send the built deployment request
    And pods are running for the replication controller "mysql"
    Then the component "mysql" should be deployed with the command "echo hello"
