Feature:
  In order to scale my applications
  As a developer
  I want to be able to scale the service by using pipe or in a decoupled manner

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

  Scenario: I can set the number of replicas
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
          "port_mappings": [
          ],
          "environment_variables": [
            {
              "name": "MYSQL_PASSWORD",
              "value": "root"
            }
          ],
          "volumes": [
          ],
          "volume_mounts": [
          ]
        },
        "extensions": [
        ],
        "labels": [
        ],
        "locked": false
      }
    ]
    """
    Then the replication controller "app" should be created
    And 5 pods of the replication controller "app" should be running

  Scenario: It should updates the RC's size if explicitly set
    Given I have an existing replication controller "app" with 2 replicas
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
            "number_of_replicas": 6
          },
          "port_mappings": [
          ],
          "environment_variables": [
            {
              "name": "MYSQL_PASSWORD",
              "value": "root"
            }
          ],
          "volumes": [
          ],
          "volume_mounts": [
          ]
        },
        "extensions": [
        ],
        "labels": [
        ],
        "locked": false
      }
    ]
    """
    Then the replication controller "app" should be updated
    And 6 pods of the replication controller "app" should be running

  Scenario: The default number of replicas is 1
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
            "enabled": true
          },
          "port_mappings": [
          ],
          "environment_variables": [
            {
              "name": "MYSQL_PASSWORD",
              "value": "root"
            }
          ],
          "volumes": [
          ],
          "volume_mounts": [
          ]
        },
        "extensions": [
        ],
        "labels": [
        ],
        "locked": false
      }
    ]
    """
    Then the replication controller "app" should be created
    And 1 pods of the replication controller "app" should be running

  Scenario: It should not updates the number of replicas if not set in request
    Given I have an existing replication controller "app" with 2 replicas
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
            "enabled": true
          },
          "port_mappings": [
          ],
          "environment_variables": [
            {
              "name": "MYSQL_PASSWORD",
              "value": "root"
            }
          ],
          "volumes": [
          ],
          "volume_mounts": [
          ]
        },
        "extensions": [
        ],
        "labels": [
        ],
        "locked": false
      }
    ]
    """
    Then the replication controller "app" should be updated
    And 2 pods of the replication controller "app" should be running
