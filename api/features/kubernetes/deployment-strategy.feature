Feature:
  In order to ensure that the component is correctly deployed
  As an API consumer
  I want to be able to precise how the things will be deployed and optionally how

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

  Scenario: It creates a lock component when the environment do not exists
    When the specification come from the template "simple-app"
    And I send the built deployment request
    Then the replication controller "mysql" should be created

  Scenario: It do not update an existing component
    Given I have an existing replication controller "mysql"
    And I have an existing replication controller "app"
    When the specification come from the template "simple-app"
    And I send the built deployment request
    Then the replication controller "mysql" shouldn't be updated

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

  Scenario: It creates the different probes successfully
    When I send a deployment request with the following components specification:
    """
    [
      {
        "name": "app",
        "identifier": "app",
        "specification": {
          "source": {
            "image": "sroze\/php-example"
          },
          "accessibility": {
            "from_cluster": true,
            "from_external": false
          },
          "scalability": {
            "enabled": true,
            "number_of_replicas": 5
          }
        },
        "deployment_strategy": {
          "liveness_probe": {
            "type": "http",
            "path": "/healthz",
            "port": 80,
            "period_seconds": 15,
            "failure_threshold": 1
          },
          "readiness_probe": {
            "type": "http",
            "path": "/healthz",
            "port": 80,
            "initial_delay_seconds": 10,
            "success_threshold": 2
          }
        }
      }
    ]
    """
    Then the replication controller "app" should be created
    And the liveness probe of the replication controller "app" should be an HTTP request at "/healthz" on port 80
    And the liveness probe of the replication controller "app" should run every 15 seconds
    And the liveness probe of the replication controller "app" should fail after 1 failure
    And the readiness probe of the replication controller "app" should be an HTTP request at "/healthz" on port 80
    And the readiness probe of the replication controller "app" should start after 10 seconds
    And the readiness probe of the replication controller "app" should success after 2 success

  Scenario: It creates TCP and EXEC probes successfully
    When I send a deployment request with the following components specification:
    """
    [
      {
        "name": "app",
        "identifier": "app",
        "specification": {
          "source": {
            "image": "sroze\/php-example"
          },
          "accessibility": {
            "from_cluster": true,
            "from_external": false
          },
          "scalability": {
            "enabled": true,
            "number_of_replicas": 5
          }
        },
        "deployment_strategy": {
          "liveness_probe": {
            "type": "tcp",
            "port": 80
          },
          "readiness_probe": {
            "type": "exec",
            "command": ["sh", "-c", "echo hello"]
          }
        }
      }
    ]
    """
    Then the replication controller "app" should be created
    And the liveness probe of the replication controller "app" should be a TCP probe on port 80
    And the readiness probe of the replication controller "app" should be an EXEC probe with the command "sh,-c,echo hello"
