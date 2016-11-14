Feature:

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
    Given the ingress "https" will be created with the public DNS address "app.my.dns"
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
    Then the deployment should be successful
    And the deployment should contain the endpoint "app.my.dns"
    When I request the environment list of the cluster "my-cluster" of the team "my-team"
    Then the status of the component "app" should contain the public endpoint "app.my.dns"
