Feature:
  In order to have better endpoint addresses
  As a user
  I want to be able to create DNS zone in CloudFlare for every endpoint

  Background:
    Given I am authenticated
    And the bucket of the team "my-team" is the bucket "00000000-0000-0000-0000-000000000000"
    And there is a cluster in the bucket "00000000-0000-0000-0000-000000000000" with the following configuration:
      | identifier | type       | address         | version | username | password |
      | my-cluster | kubernetes | https://1.2.3.4 | v1      | username | password |
    And I am building a deployment request
    And the target environment name is "master"
    And the target cluster identifier is "my-cluster"
    And the credentials bucket is "00000000-0000-0000-0000-000000000000"
    And the pods of the replication controllers will be created successfully and running

  Scenario: It creates a A zone in CloudFlare
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
            "name": "http",
            "cloud_flare_zone": {
              "zone_identifier": "1234531235",
              "record_suffix": ".example.com",
              "authentication": {
                "email": "samuel@example.com",
                "api_key": "foobar"
              }
            }
          }
        ]
      }
    ]
    """
    When I send the built deployment request
    Then the service "http" should be created
    And the CloudFlare zone "master.example.com" should have been created with the type A and the address "1.2.3.4"

  Scenario: It creates a DNS zone in CloudFlare
    Given the service "http" will be created with the public DNS address "112345.elb.aws.com"
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
            "name": "http",
            "cloud_flare_zone": {
              "zone_identifier": "1234531235",
              "record_suffix": "-myapp.example.com",
              "authentication": {
                "email": "samuel@example.com",
                "api_key": "foobar"
              }
            }
          }
        ]
      }
    ]
    """
    When I send the built deployment request
    Then the service "http" should be created
    And the CloudFlare zone "master-myapp.example.com" should have been created with the type CNAME and the address "112345.elb.aws.com"
