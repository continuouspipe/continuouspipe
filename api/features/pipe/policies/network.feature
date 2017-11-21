Feature:
  In order to deploy containers that are not available to be talked to/from
  As a user
  I want to be able to use network policies

  Background:
    Given I am authenticated
    And there is a cluster in the bucket "00000000-0000-0000-0000-000000000000" with the following configuration:
      | identifier | type       | address         | version | username | password |
      | my-cluster | kubernetes | https://1.2.3.4 | v1.7    | username | password |
    And the user credentials of the cluster "my-cluster" of bucket "00000000-0000-0000-0000-000000000000" is a Google Cloud service account for the user "a-team@continuouspipe-flex.iam.gserviceaccount.com"
    And I am building a deployment request
    And the target environment name is "my-environment"
    And the target cluster identifier is "my-cluster"
    And the credentials bucket is "00000000-0000-0000-0000-000000000000"
    And the pods of the deployment app will be running after creation

  Scenario: It allows communication between pods in the namespace
    Given the cluster "my-cluster" of the bucket "00000000-0000-0000-0000-000000000000" has the "network" policy with the following configuration:
    """
    {
      "rules": [
        {"type": "allow-current-namespace"}
      ]
    }
    """
    When I send a deployment request with the following components specification:
    """
    [
      {
        "name": "app",
        "identifier": "app",
        "specification": {
          "source": {
            "image": "sroze\/php-example"
          }
        }
      }
    ]
    """
    Then the deployment "app" should be created
    And the deployment should be successful
    And the network policy "allow-current-namespace" should be created
    And the network policy "allow-current-namespace" should have no egress configuration
    And the network policy "allow-current-namespace" should have an ingress rule from "continuous-pipe-environment=my-environment"
