Feature:
  In order to be able to use HttpLabs' reverse proxy and its features
  As a user
  I want to be able to create an HttpLabs proxy

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

  Scenario: It proxies through the created endpoint
    Given the service "http" will be   created with the public IP "1.2.3.4"
    And the created HttpLabs stack will have the UUID "00000000-0000-0000-0000-000000000000" and the URL address "https://foo-bar.httplabs.io"
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
            "httplabs": {
              "api_key": "cdba7ddb-06ac-47f8-b389-0819b48a2ee8",
              "project_identifier": "13d1ab08-0eca-4289-aa8b-132bc569fe3f"
            }
          }
        ]
      }
    ]
    """
    When I send the built deployment request
    Then the service "http" should be created
    And the service "http" should have the type "LoadBalancer"
    And an HttpLabs stack should have been created with the backend "http://1.2.3.4"
    And the annotation "com.continuouspipe.io.httplabs.stack" of the service "http" should contain the following keys in its JSON:
      | name             | value                                |
      | stack_identifier | 00000000-0000-0000-0000-000000000000 |
      | stack_address    | foo-bar.httplabs.io                  |
    And the deployment should contain the endpoint "foo-bar.httplabs.io"
    And the deployment endpoint "foo-bar.httplabs.io" should have the port "80"
    And the HttpLabs stack "00000000-0000-0000-0000-000000000000" should have been deployed

  Scenario: It reuses the created HttpLabs stack and update it
    Given there is a service "http" for the component "app"
    And the HttpLabs stack "00000000-0000-0000-0000-000000000000" will be successfully configured
    And the service "http" have the selector "component-identifier=app" and type "LoadBalancer" with the ports:
      | name | port | protocol | targetPort |
      | http | 80   | tcp      | 80         |
    And the service "http" have the public IP "1.2.3.4"
    And the service "http" have the following annotations:
      | name                                 | value                                                                                             |
      | com.continuouspipe.io.httplabs.stack | {"stack_identifier":"00000000-0000-0000-0000-000000000000","stack_address":"foo-bar.httplabs.io"} |
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
            "httplabs": {
              "api_key": "cdba7ddb-06ac-47f8-b389-0819b48a2ee8",
              "project_identifier": "13d1ab08-0eca-4289-aa8b-132bc569fe3f"
            }
          }
        ]
      }
    ]
    """
    When I send the built deployment request
    Then the service "http" should not be created
    And the service "http" should not be updated
    And an HttpLabs stack should not have been created
    And the stack "00000000-0000-0000-0000-000000000000" should have been updated
    And the deployment should contain the endpoint "foo-bar.httplabs.io"
    And the deployment endpoint "foo-bar.httplabs.io" should have the port "80"

  Scenario: It creates the middle-wares
    Given the service "http" will be created with the public IP "1.2.3.4"
    And the created HttpLabs stack will have the UUID "00000000-0000-0000-0000-000000000000" and the URL address "https://foo-bar.httplabs.io"
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
            "httplabs": {
              "api_key": "cdba7ddb-06ac-47f8-b389-0819b48a2ee8",
              "project_identifier": "13d1ab08-0eca-4289-aa8b-132bc569fe3f",
              "middlewares": [
                {
                  "template": "https://api.httplabs.io/projects/13d1ab08-0eca-4289-aa8b-132bc569fe3f/templates/basic_authentication",
                  "config": {
                    "realm":"This is a restricted area",
                    "username":"username",
                    "password":"password"
                  }
                },
                {
                  "template":"https://api.httplabs.io/projects/13d1ab08-0eca-4289-aa8b-132bc569fe3f/templates/ip_restrict",
                  "config":{
                    "ips": ["217.138.5.218", "217.138.5.2"]
                  }
                }
              ]
            }
          }
        ]
      }
    ]
    """
    When I send the built deployment request
    Then an HttpLabs stack should have been created with the backend "http://1.2.3.4"
    And a middleware from the template "https://api.httplabs.io/projects/13d1ab08-0eca-4289-aa8b-132bc569fe3f/templates/basic_authentication" should have been created on the stack "00000000-0000-0000-0000-000000000000" with the following configuration:
    """
    {
      "realm":"This is a restricted area",
      "username":"username",
      "password":"password"
    }
    """
    And a middleware from the template "https://api.httplabs.io/projects/13d1ab08-0eca-4289-aa8b-132bc569fe3f/templates/ip_restrict" should have been created on the stack "00000000-0000-0000-0000-000000000000" with the following configuration:
    """
    {
      "ips": ["217.138.5.218", "217.138.5.2"]
    }
    """

  Scenario: It remove the existing middlewares when updating
    Given there is a service "http" for the component "app"
    And the HttpLabs stack "00000000-0000-0000-0000-000000000000" will be successfully configured
    And the HttpLabs stack "00000000-0000-0000-0000-000000000000" have the following middlewares:
      | identifier                           | template                                                                                                            | config                                                                                 |
      | 00000000-0000-0000-0000-000000000001 | https://messenger-art-8717.httplabs.io/projects/13d1ab08-0eca-4289-aa8b-132bc569fe3f/templates/basic_authentication | {"realm": "This is a restricted area", "username": "username","password": "password2"} |
      | 00000000-0000-0000-0000-000000000002 | https://messenger-art-8717.httplabs.io/projects/13d1ab08-0eca-4289-aa8b-132bc569fe3f/templates/ip_restrict          | {"ips": ["217.138.5.218", "217.138.5.2"]}                                              |
    And the service "http" have the selector "component-identifier=app" and type "LoadBalancer" with the ports:
      | name | port | protocol | targetPort |
      | http | 80   | tcp      | 80         |
    And the service "http" have the public IP "1.2.3.4"
    And the service "http" have the following annotations:
      | name                                 | value                                                                                             |
      | com.continuouspipe.io.httplabs.stack | {"stack_identifier":"00000000-0000-0000-0000-000000000000","stack_address":"foo-bar.httplabs.io"} |
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
            "httplabs": {
              "api_key": "cdba7ddb-06ac-47f8-b389-0819b48a2ee8",
              "project_identifier": "13d1ab08-0eca-4289-aa8b-132bc569fe3f",
              "middlewares": [
                {
                  "template": "https://api.httplabs.io/projects/13d1ab08-0eca-4289-aa8b-132bc569fe3f/templates/basic_authentication",
                  "config": {
                    "realm":"This is a restricted area",
                    "username":"username",
                    "password":"password"
                  }
                }
              ]
            }
          }
        ]
      }
    ]
    """
    When I send the built deployment request
    Then the service "http" should not be created
    And the stack "00000000-0000-0000-0000-000000000000" should have been updated
    And a middleware from the template "https://api.httplabs.io/projects/13d1ab08-0eca-4289-aa8b-132bc569fe3f/templates/basic_authentication" should have been created on the stack "00000000-0000-0000-0000-000000000000" with the following configuration:
    """
    {
      "realm":"This is a restricted area",
      "username":"username",
      "password":"password"
    }
    """
    And the middleware "00000000-0000-0000-0000-000000000001" from the stack "00000000-0000-0000-0000-000000000000" should have been removed
    And the middleware "00000000-0000-0000-0000-000000000002" from the stack "00000000-0000-0000-0000-000000000000" should have been removed
    And the HttpLabs stack "00000000-0000-0000-0000-000000000000" should have been deployed

  Scenario: It proxies through the internal endpoint
    Given the created HttpLabs stack will have the UUID "00000000-0000-0000-0000-000000000000" and the URL address "https://foo-bar.httplabs.io"
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
            "type": "internal",
            "httplabs": {
              "api_key": "cdba7ddb-06ac-47f8-b389-0819b48a2ee8",
              "project_identifier": "13d1ab08-0eca-4289-aa8b-132bc569fe3f"
            }
          }
        ]
      }
    ]
    """
    When I send the built deployment request
    Then the service "http" should be created
    And the service "http" should have the type "ClusterIP"
    And an HttpLabs stack should have been created with the backend "http://http.master.cluster.svc.local"
    And the deployment should contain the endpoint "foo-bar.httplabs.io"
    And the HttpLabs stack "00000000-0000-0000-0000-000000000000" should have been deployed
