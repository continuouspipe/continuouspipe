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

  Scenario: It will rolling-update with a 0 max-unavailable if no volume
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
        }
      }
    ]
    """
    Then the deployment "app" should be rolling updated with maximum 0 unavailable pods

  Scenario: It will rolling-update with a 1 max-unavailable if some volume
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
          },
          "volumes": [
            {
              "type": "persistent",
              "name": "app-volume",
              "capacity": "5Gi",
              "storage_class": "my-class"
            }
          ],
          "volume_mounts": [
            {
              "name": "app-volume",
              "mount_path": "/app/shared"
            }
          ]
        }
      }
    ]
    """
    Then the deployment "app" should be rolling updated with maximum 1 unavailable pods

  Scenario: It will rolling-update with the specified max-available and max-surge parameters
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
          "max_unavailable": 2,
          "max_surge": 2
        }
      }
    ]
    """
    Then the deployment "app" should be rolling updated with maximum 2 unavailable pods
    And the deployment "app" should be rolling updated with maximum 2 surge pods
