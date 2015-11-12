Feature:
  In order to prevent updating some components of the application that rely on datas
  As a devops guy
  I want to be able to lock component update

  Background:
    Given I am authenticated
    And I am building a deployment request
    And the target environment name is "my-environment"
    And the target cluster identifier is "my-cluster"
    And the credentials bucket is "00000000-0000-0000-0000-000000000000"
    And there is a cluster in the bucket "00000000-0000-0000-0000-000000000000" with the following configuration:
      | identifier | type       | address         | version | username | password |
      | my-cluster | kubernetes | https://1.2.3.4 | v1      | username | password |
    And the pods of the replication controllers will be created successfully and running

  Scenario:
    When the specification come from the template "simple-app"
    And I send the built deployment request
    Then the replication controller "mysql" should be created

  Scenario:
    Given I have an existing replication controller "mysql"
    And I have an existing replication controller "app"
    When the specification come from the template "simple-app"
    And I send the built deployment request
    Then the replication controller "mysql" shouldn't be updated
