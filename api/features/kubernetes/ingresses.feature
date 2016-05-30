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

  Scenario: Creates an ingress with SSL
    Given the components specification are:
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
            "name": "https",
            "ssl_certificates": [
              {"name": "continuous-pipe", "cert": "...", "key": "..."}
            ]
          }
        ]
      }
    ]
    """
    When I send the built deployment request
    Then the service "https" should be created
    And the service "https" should have the type "ClusterIP"
    And the ingress named "https" should be created
    And the ingress named "https" should have 1 SSL certificate

  Scenario: Creates other type of services
    Given the components specification are:
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
            "name": "http",
            "type": "NodePort"
          }
        ]
      }
    ]
    """
    When I send the built deployment request
    Then the service "http" should be created
    And the service "http" should have the type "NodePort"
    And the ingress named "http" should be created
