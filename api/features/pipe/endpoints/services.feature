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
    And the deployment endpoint "1.2.3.4" should have the port "80"

  Scenario: It fails the deployment if the service address is not resolved
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
            "name": "http"
          }
        ]
      }
    ]
    """
    When I send the built deployment request
    Then the service "http" should be created
    And the deployment should be failed

  Scenario: The public services should not be updated if the selector are the same
    Given I have a service "app" with the selector "com.continuouspipe.visibility=public,component-identifier=app" and type "LoadBalancer" with the ports:
      | name | port | protocol | targetPort |
      | http | 80   | tcp      | 80         |
    And the service "app" will be created with the public IP "1.2.3.4"
    When the specification come from the template "simple-app-public"
    And I send the built deployment request
    Then the service "app" should not be updated
    And the service "app" should not be deleted
    And the service "app" should not be created

  Scenario: The public services should be updated if selectors are different
    Given I have a service "app" with the selector "component-identifier=app" and type "LoadBalancer" with the ports:
      | name | port | protocol | targetPort |
      | http | 80   | tcp      | 80         |
    And the service "app" will be created with the public IP "1.2.3.4"
    When the specification come from the template "simple-app-public"
    And I send the built deployment request
    Then the service "app" should be updated

  Scenario: The public services should be updated if type is different
    Given I have a service "app" with the selector "com.continuouspipe.visibility=public,component-identifier=app" and type "ClusterIP" with the ports:
      | name | port | protocol | targetPort |
      | http | 80   | tcp      | 80         |
    And the service "app" will be created with the public IP "1.2.3.4"
    When the specification come from the template "simple-app-public"
    And I send the built deployment request
    Then the service "app" should be updated

  Scenario: The public services should be updated if ports are different
    Given I have a service "app" with the selector "com.continuouspipe.visibility=public,component-identifier=app" and type "LoadBalancer" with the ports:
      | name  | port | protocol | targetPort |
      | http  | 80   | tcp      | 80         |
      | https | 443  | tcp      | 443        |
    And the service "app" will be created with the public IP "1.2.3.4"
    When the specification come from the template "simple-app-public"
    And I send the built deployment request
    Then the service "app" should be updated

  Scenario: LoadBalancer services annotations default endpoints
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
            "annotations": {
              "service.beta.kubernetes.io/external-traffic": "OnlyLocal"
            }
          }
        ]
      }
    ]
    """
    When I send the built deployment request
    Then the service "http" should be created
    And the service "http" should have the type "LoadBalancer"
    And the service "http" should have the following annotations:
      | name                                        | value     |
      | service.beta.kubernetes.io/external-traffic | OnlyLocal |

  Scenario: NodePort services directly have endpoints
    Given the service "http" will have the port 80 mapped to the node port 1234
    And the cluster "my-cluster" of the bucket "00000000-0000-0000-0000-000000000000" has the "endpoint" policy with the following configuration:
    """
    {
      "type": "NodePort",
      "node-port-address": "localhost"
    }
    """
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
    And the ingress named "http" should not be created
    And the deployment endpoint "localhost" should have the port "1234"

  Scenario: When the component and service have the same name, the endpoint service should be used
    Given the service "app" will be created with the public IP "1.2.3.4"
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
          "accessibility": {
            "from_cluster":true
          },
          "ports": [
            {"identifier": "http", "port": 80, "protocol": "TCP"}
          ]
        },
        "endpoints": [
          {
            "name": "app"
          }
        ]
      }
    ]
    """
    When I send the built deployment request
    Then the service "app" should be created
    And the service "app" should have the type "LoadBalancer"
    And the deployment endpoint "1.2.3.4" should have the port "80"
