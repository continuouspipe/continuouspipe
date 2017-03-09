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
    And the annotation "com.continuouspipe.io.cloudflare.zone" of the service "http" should contain the following keys in its JSON:
      | name              | value              |
      | record_name       | master.example.com |
      | record_identifier | 1234               |

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
              "zone_identifier": "1234531235qwerty",
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
    And the annotation "com.continuouspipe.io.cloudflare.zone" of the service "http" should contain the following keys in its JSON:
      | name              | value                    |
      | record_name       | master-myapp.example.com |
      | record_identifier | 1234                     |
      | zone_identifier   | 1234531235qwerty         |
    And the annotation "com.continuouspipe.io.cloudflare.zone" of the service "http" should contain the JSON key "encrypted_authentication"

  Scenario: It still returns the port in the endpoints
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
              "zone_identifier": "1234531235qwerty",
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
    And the deployment endpoint "master-myapp.example.com" should have the port "80"

  Scenario: It returns the CloudFlare endpoint even if the endpoint was already created
    Given I have a service "http" with the selector "component-identifier=app" and type "LoadBalancer" with the ports:
      | name | port | protocol | targetPort |
      | http | 80   | tcp      | 80         |
    And the service "http" have the public IP "1.2.3.4"
    And the service "http" have the following annotations:
      | name                                  | value                                                                 |
      | com.continuouspipe.io.cloudflare.zone | {"record_name":"master-myapp.example.com","record_identifier":"1234"} |
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
              "zone_identifier": "1234531235qwerty",
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
    And the deployment endpoint "master-myapp.example.com" should have the port "80"

  Scenario: It removes the CF record when the environment is deleted
    Given I have a namespace "app"
    And I have a service "http" with the selector "component-identifier=app" and type "LoadBalancer" with the ports:
      | name | port | protocol | targetPort |
      | http | 80   | tcp      | 80         |
    And the service "http" have the public IP "1.2.3.4"
    And the service "http" have the following annotations:
      | name                                  | value                                                                                                                                   |
      | com.continuouspipe.io.cloudflare.zone | {"record_name":"master-myapp.example.com","record_identifier":"1234","zone_identifier":"9876","encrypted_authentication":"SECRET_AUTH"} |
    And the encrypted value "SECRET_AUTH" in the namespace "9876-1234" will be decrypted as the following by the vault:
    """
    {"api_key":"1234","email":"my@example.com"}
    """
    When I delete the environment named "app" of the cluster "my-cluster" of the team "my-team"
    Then the namespace should be deleted successfully
    And the CloudFlare record "1234" of the zone "9876" should have been deleted
