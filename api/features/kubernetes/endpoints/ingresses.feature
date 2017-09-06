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
    Then the service "https" should be created
    And the service "https" should have the type "ClusterIP"
    And the ingress named "https" should be created
    And the ingress named "https" should have 1 SSL certificate
    And the deployment should contain the endpoint "app.my.dns"
    And the secret "https-continuous-pipe" should be created

  Scenario: Creates an ingress with the "ingress" type
    Given the ingress "www" will be created with the public DNS address "app.my.dns"
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
            "name": "www",
            "type": "ingress"
          }
        ]
      }
    ]
    """
    When I send the built deployment request
    Then the service "www" should be created
    And the service "www" should have the type "NodePort"
    And the ingress named "www" should be created
    And the deployment should be successful

  Scenario: Creates other type of services
    Given the ingress "http" will be created with the public DNS address "app.my.dns"
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

  Scenario: The existing services should also be waited and their endpoints fetched
    Given I have a service "app" with the selector "com.continuouspipe.visibility=public,component-identifier=app" and type "LoadBalancer" with the ports:
      | name | port | protocol | targetPort |
      | http | 80   | tcp      | 80         |
    And the service "app" will be created with the public IP "1.2.3.4"
    When the specification come from the template "simple-app-public"
    And I send the built deployment request
    Then the service "app" should not be updated
    And the deployment should contain the endpoint "1.2.3.4"

  Scenario: It returns the port number of existing services
    Given I have a service "app" with the selector "com.continuouspipe.visibility=public,component-identifier=app" and type "LoadBalancer" with the ports:
      | name | port | protocol | targetPort |
      | http | 80   | tcp      | 80         |
    And the service "app" will be created with the public IP "1.2.3.4"
    When the specification come from the template "simple-app-public"
    And I send the built deployment request
    Then the service "app" should not be updated
    And the deployment should contain the endpoint "1.2.3.4"
    And the deployment endpoint "1.2.3.4" should have the port "80"

  Scenario: It fails if the ingress do not have an address
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
            "name": "www",
            "type": "ingress"
          }
        ]
      }
    ]
    """
    When I send the built deployment request
    Then the ingress named "www" should be created
    And the deployment should be failed

  Scenario: Create an ingress with hostname
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
            "name": "www",
            "ingress": {
              "class": "nginx",
              "rules": [
                {
                  "host": "app-www.continuouspipe.net"
                }
              ]
            }
          }
        ]
      }
    ]
    """
    When I send the built deployment request
    Then the ingress named "www" should be created
    And the service "www" should have the type "ClusterIP"
    And the ingress named "www" should have the hostname "app-www.continuouspipe.net"
    And the ingress named "www" should have the class "nginx"
    And the ingress named "www" should not have a backend service
    And the ingress named "www" should not be using secure backends

  Scenario: It returns the ingress hosts in the endpoints
    Given the ingress "www" will be created with the public IP "1.2.3.4"
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
            "name": "www",
            "ingress": {
              "class": "nginx",
              "rules": [
                {
                  "host": "app-yves.continuouspipe.net"
                },
                {
                  "host": "app-zed.continuouspipe.net"
                }
              ]
            }
          }
        ]
      }
    ]
    """
    When I send the built deployment request
    Then the ingress named "www" should be created
    And the deployment should contain the endpoint "app-yves.continuouspipe.net"
    And the deployment should contain the endpoint "app-zed.continuouspipe.net"

  Scenario: The ingress should use secure backends if the component exposes the port 443
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
            {"identifier": "https", "port": 443, "protocol": "TCP"}
          ]
        },
        "endpoints": [
          {
            "name": "www",
            "ingress": {
              "class": "nginx",
              "rules": [
                {
                  "host": "app-www.continuouspipe.net"
                }
              ]
            }
          }
        ]
      }
    ]
    """
    When I send the built deployment request
    Then the ingress named "www" should be created
    And the ingress named "www" should have the hostname "app-www.continuouspipe.net"
    And the ingress named "www" should not have a backend service
    And the ingress named "www" should have the backend service "www" on port "443" behind the rule "app-www.continuouspipe.net"
    And the ingress named "www" should be using secure backends

  Scenario: The ingress should use the provided SSL certificates
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
            {"identifier": "https", "port": 443, "protocol": "TCP"}
          ]
        },
        "endpoints": [
          {
            "name": "www",
            "ssl_certificates": [
              {"name": "continuous-pipe", "cert": "...", "key": "..."}
            ],
            "ingress": {
              "class": "nginx",
              "rules": [
                {
                  "host": "app-www.continuouspipe.net"
                }
              ]
            }
          }
        ]
      }
    ]
    """
    When I send the built deployment request
    Then the ingress named "www" should be created
    And the ingress named "www" should have the hostname "app-www.continuouspipe.net"
    And the ingress named "www" should not have a backend service
    And the ingress named "www" should have a SSL certificate for the host "app-www.continuouspipe.net"

  Scenario: Do not update secrets if the data is the same
    Given the ingress "https" will be created with the public DNS address "app.my.dns"
    And the secret of type "Opaque" named "https-continuous-pipe" already exists with the following data:
      | tls.crt | ... |
      | tls.key | ... |
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
    Then the secret "https-continuous-pipe" should not be updated
    And the secret "https-continuous-pipe" should not be created

  Scenario: Updates the secrets if the data changed
    Given the ingress "https" will be created with the public DNS address "app.my.dns"
    And the secret of type "Opaque" named "https-continuous-pipe" already exists with the following data:
      | tls.crt | ... |
      | tls.key | ... |
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
              {"name": "continuous-pipe", "cert": "NEW", "key": "NEW"}
            ]
          }
        ]
      }
    ]
    """
    When I send the built deployment request
    Then the secret "https-continuous-pipe" should be updated

  Scenario: Create a empty ingress
    Given the ingress "www" will be created with the public DNS address "app.my.dns"
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
            "name": "www",
            "ingress": {}
          }
        ]
      }
    ]
    """
    When I send the built deployment request
    Then the ingress named "www" should be created
