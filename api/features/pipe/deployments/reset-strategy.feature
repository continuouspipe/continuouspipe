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
      | my-cluster | kubernetes | https://1.2.3.4 | v1.3    | username | password |
    And the pods of the replication controllers will be created successfully and running
    And the pods of the deployment "app" will be running after creation

  Scenario: It will remove the existing pods before deploying
    Given there is a pod named "app-1234" labelled "component-identifier=app"
    And there is a pod named "database-5678" labelled "component-identifier=database"
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
          "reset": true
        }
      }
    ]
    """
    Then the pod "app-1234" should be deleted
    Then the pod "database-5678" should not be deleted

  Scenario: Will not remove the existing pods before deploying if
    Given there is a pod named "app-1234" labelled "component-identifier=app"
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
          "reset": false
        }
      }
    ]
    """
    Then the pod "app-1234" should not be deleted
