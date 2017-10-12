Feature:
  In order to deployed containers on am RBAC-enabled cluster
  As a DevOps engineer
  I want to tell to CP that the cluster has RBAC and CP will do the rest.

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

  Scenario: It creates policies and uses them when the cluster has RBAC policy
    Given the cluster "my-cluster" of the bucket "00000000-0000-0000-0000-000000000000" has the "rbac" policy with the following configuration:
    """
    {
      "cluster-role": "managed-user"
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
    And the user "a-team@continuouspipe-flex.iam.gserviceaccount.com" should be bound to the cluster role "managed-user" in the namespace "app"
    And the deployment should be successful

  Scenario: It won't create the policy if it exists
    Given the cluster "my-cluster" of the bucket "00000000-0000-0000-0000-000000000000" has the "rbac" policy with the following configuration:
    """
    {
      "cluster-role": "managed-user"
    }
    """
    And the namespace contains a role binding named "team-service-account-is-managed-used"
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
    And no role binding should be created
    And the deployment should be successful
