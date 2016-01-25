Feature:
  In order to have an overview of the deployed environments
  As a developer
  I want to have the list and the status of each environment deployed in different namespaces

  Background:
    Given I am authenticated
    And the bucket of the team "my-team" is the bucket "00000000-0000-0000-0000-000000000000"
    And there is a cluster in the bucket "00000000-0000-0000-0000-000000000000" with the following configuration:
      | identifier | type       | address         | version | username | password |
      | my-cluster | kubernetes | https://1.2.3.4 | v1      | username | password |

    And I am building a deployment request
    And the target environment name is "my-environment"
    And the target cluster identifier is "my-cluster"
    And the credentials bucket is "00000000-0000-0000-0000-000000000000"
    And the specification come from the template "simple-app"
    And the pods of the replication controllers will be created successfully and running
    When I send the built deployment request
    Then the deployment should be successful

  Scenario: I get the list of running components
    When I request the environment list of the cluster "my-cluster" of the team "my-team"
    Then I should see the component "app"
    And I should see the component "mysql"

  Scenario:
    Given pods are running but not ready for the replication controller "app"
    When I request the environment list of the cluster "my-cluster" of the team "my-team"
    Then the status of the component "app" should be "unhealthy"

  Scenario:
    Given pods are pending for the replication controller "app"
    When I request the environment list of the cluster "my-cluster" of the team "my-team"
    Then the status of the component "app" should be "unhealthy"

  Scenario:
    Given pods are running for the replication controller "app"
    When I request the environment list of the cluster "my-cluster" of the team "my-team"
    Then the status of the component "app" should be "healthy"

  Scenario: The IP of the service should be in the status
    Given the service "app" have the public IP "1.2.3.4"
    When I request the environment list of the cluster "my-cluster" of the team "my-team"
    Then the status of the component "app" should contain the public endpoint "1.2.3.4"

  Scenario: The hostname of the service should be in the status
    Given the service "app" have the public hostname "foo.bar.dns"
    When I request the environment list of the cluster "my-cluster" of the team "my-team"
    Then the status of the component "app" should contain the public endpoint "foo.bar.dns"
