Feature:
  In order to have isolated running environments
  As a developer
  Each environment have to run in isolated namespaces

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

  Scenario: Reuse existing namespace
    Given I have a namespace "my-environment"
    When I send the built deployment request
    Then it should not create any namespace

  Scenario: Delete an environment should delete the namespace
    Given I have a namespace "foo"
    When I delete the environment named "foo" of the cluster "my-cluster" of the team "my-team"
    Then the namespace "foo" should be deleted

  Scenario: Update namespace' service account after creating namespace
    When I send the built deployment request
    Then a docker registry secret should be created
    And the service account should be updated with a docker registry pull secret

  Scenario: Update service account if secret not found
    Given I have a namespace "my-environment"
    And the service account "default" to not contain any docker registry pull secret
    When I send the built deployment request
    Then a docker registry secret should be created
    And the service account should be updated with a docker registry pull secret

  Scenario: Wait namespace's service account to be created
    Given the default service account won't be created at the same time than the namespace
    When I send the built deployment request
    Then the service account should be updated with a docker registry pull secret
