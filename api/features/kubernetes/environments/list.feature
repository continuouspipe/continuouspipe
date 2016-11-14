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
    And the environment label "flow" contains "1234567890"
    And the environment label "tide" contains "0987654321"
    And the pods of the replication controllers will be created successfully and running
    When I send the built deployment request
    Then the deployment should be successful

  Scenario: List of environments for a given label
    When I request the environment list of the cluster "my-cluster" of the team "my-team" that have the labels "flow=1234567890"
    Then I should see the environment "my-environment"

  Scenario: List of environments with many labels
    When I request the environment list of the cluster "my-cluster" of the team "my-team" that have the labels "flow=1234567890,tide=0987654321"
    Then I should see the environment "my-environment"

  Scenario: List of enviroments with non-matching labels
    When I request the environment list of the cluster "my-cluster" of the team "my-team" that have the labels "flow=0987654321"
    Then I should not see the environment "my-environment"

  Scenario: List of running components
    When I request the environment list of the cluster "my-cluster" of the team "my-team"
    Then I should see the component "app"
    And I should see the component "mysql"

  Scenario: The IP of the service should be in the status
    Given the service "app" have the public IP "1.2.3.4"
    When I request the environment list of the cluster "my-cluster" of the team "my-team"
    Then the status of the component "app" should contain the public endpoint "1.2.3.4"

  Scenario: The hostname of the service should be in the status
    Given the service "app" have the public hostname "foo.bar.dns"
    When I request the environment list of the cluster "my-cluster" of the team "my-team"
    Then the status of the component "app" should contain the public endpoint "foo.bar.dns"

  Scenario: It returns the status of the containers
    When I request the environment list of the cluster "my-cluster" of the team "my-team"
    Then the status of the component "app" should contain container "app-1"
