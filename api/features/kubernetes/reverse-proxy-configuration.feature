Feature:
  In order to manage the domain names used for the public endpoints
  As a developer
  If the target cluster is using kubernetes-reverse-proxy or kubernetes-vamp-router
  I want to be able to configure the domain names that will be used

  Background:
    Given I am authenticated
    And I am building a deployment request
    And the target environment name is "my-environment"
    And the target cluster identifier is "my-cluster"
    And the credentials bucket is "00000000-0000-0000-0000-000000000000"
    And there is a cluster in the bucket "00000000-0000-0000-0000-000000000000" with the following configuration:
      | identifier | type       | address         | version | username | password |
      | my-cluster | kubernetes | https://1.2.3.4 | v1      | username | password |

  Scenario: I can uses an image name including the registry name
    Given the specification come from the template "public-container-with-reverse-proxy-configuration"
    And the service "app" will be created with the public IP "1.2.3.4"
    And the pods of the replication controller "app" will be running after creation
    When I send the built deployment request
    Then the service "app" should be created
    And the service "app" should contain the following annotations:
      | name                   | value                                          |
      | kubernetesReverseProxy | {"hosts":[{"host":"example.com","port":"80"}]} |
