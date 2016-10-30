Feature:
  In order to have a access to the deployed services
  As a user
  I want to be able to create ingresses

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
    And the pods of the replication controllers will be created successfully and running

  Scenario: Creates a load balancer service by default
    Given the service "http" will be created with the public IP "1.2.3.4"
    And the components specification are:
    """
    [
      {
        "name": "app",
        "identifier": "app",
        "specification": {
          "source": {
            "image": "sroze\/php-example"
          },
          "scalability": {
            "enabled": true,
            "number_of_replicas": 1
          },
          "ports": [
            {"identifier": "http", "port": 80, "protocol": "TCP"}
          ]
        },
        "endpoints": [
          {
            "name": "http"
          }
        ]
      }
    ]
    """
    When I send the built deployment request
    Then the service "http" should be created
    And the service "http" should have the type "LoadBalancer"
    And the ingress named "http" should not be created
