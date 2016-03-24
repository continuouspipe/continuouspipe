Feature:
  In order to have containers with persistent volumes
  As a developer
  I want to be able to mount persistent volumes in my containers

  Background:
    Given I am authenticated
    And there is a cluster in the bucket "00000000-0000-0000-0000-000000000000" with the following configuration:
      | identifier | type       | address         | version | username | password |
      | my-cluster | kubernetes | https://1.2.3.4 | v1      | username | password |
    And I am building a deployment request
    And the target environment name is "my-environment"
    And the target cluster identifier is "my-cluster"
    And the credentials bucket is "00000000-0000-0000-0000-000000000000"
    And the pods of the replication controllers will be created successfully and running

  Scenario: I can uses an image name including the registry name
    Given the specification come from the template "simple-private-app"
    And the pods of the replication controller "app" will be running after creation
    When I send the built deployment request
    Then the image name of the deployed component "app" should be "docker.io/foo/bar"
    And the image tag of the deployed component "app" should be "master"

  Scenario: I can override the default command
    Given the specification come from the template "overwrite-command"
    And the pods of the replication controller "mysql" will be running after creation
    When I send the built deployment request
    Then the component "mysql" should be deployed with the command "echo hello"

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
          ]
        }
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
          ]
        }
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
          ]
        }
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
          ]
        }
      }
    ]
    """
    Then the replication controller "app" should be updated
    And 2 pods of the replication controller "app" should be running

  Scenario: The volume claim should be created if do not exists
    Given the specification come from the template "persistent-mounted-volume"
    When I send the built deployment request
    And pods are running for the replication controller "app"
    Then the volume claim "app-volume" should be created
    And the component "app" should be created with a persistent volume mounted in "/app/shared"

  Scenario: If the volume claim exists, it should reuse it
    Given there is a volume claim "app-volume"
    And the specification come from the template "persistent-mounted-volume"
    When I send the built deployment request
    And pods are running for the replication controller "app"
    Then the volume claim "app-volume" should not be created
    And the component "app" should be created with a persistent volume mounted in "/app/shared"
