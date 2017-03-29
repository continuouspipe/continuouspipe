Feature:
  In order to see logs of a running pod
  As a user
  I want to define a component as attached

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

  Scenario: It creates only the pod as the component is not scalable
    Given the pod "app" will run successfully
    And the specification come from the template "attached-component"
    When I send the built deployment request
    Then the pod "app" should be created
    And the service "app" should not be created
    And the replication controller "app" should not be created

  Scenario: It will fail the deployment if the pod exit with a status code different than 0
    Given the pod "app" will fail with exit code 1
    And the specification come from the template "attached-component"
    When I send the built deployment request
    And the deployment should be failed
    And the pod "app" should be deleted

  Scenario: If pod is successful then the deployment too
    Given the pod "app" will run successfully
    And the specification come from the template "attached-component"
    When I send the built deployment request
    And the deployment should be successful
    And the pod "app" should be deleted

  Scenario: If such pod is already running, failing with a clear error message
    Given there is a pod "app" already running
    And the specification come from the template "attached-component"
    When I send the built deployment request
    And the deployment should be failed
    And I should see a text log event in the log stream with message 'A running pod named "app" was found'

  Scenario: It such pod already exists but is completed, deletes it
    Given there is a completed pod "app"
    And the pod "app" will run successfully
    And the specification come from the template "attached-component"
    When I send the built deployment request
    And the deployment should be successful
    And the pod "app" should be deleted
