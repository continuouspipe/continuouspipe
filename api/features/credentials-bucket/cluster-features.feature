Feature:
  In order to have ContinuousPipe using optional security (and more) features of my Kubernetes cluster
  As a user
  I want to explicitly define which features are supported by the cluster

  Background:
    Given the user "samuel" have access to the bucket "00000000-0000-0000-0000-000000000000"
    And I am authenticated as user "samuel"

  Scenario: I create a cluster with some features
    Given I have the following clusters in the bucket "00000000-0000-0000-0000-000000000000":
      | identifier | type       | address         | username | password | version | features |
      | my-kube    | kubernetes | https://1.2.3.4 | samuel   | roze     | v1.4    | rbac     |
    When I ask the list of the clusters in the bucket "00000000-0000-0000-0000-000000000000"
    Then the cluster "my-kube" should have the feature rbac
    And the cluster "my-kube" should not have the feature "network-policies"

  @smoke
  Scenario: I add some features to a cluster
    Given I have the following clusters in the bucket "00000000-0000-0000-0000-000000000000":
      | identifier | type       | address         | username | password | version |
      | my-kube    | kubernetes | https://1.2.3.4 | samuel   | roze     | v1.4    |
    When I update the cluster "my-kube" of the bucket "00000000-0000-0000-0000-000000000000" with the following request:
    """
    {"features": {"rbac": true, "network-policies": true}}
    """
    And I ask the list of the clusters in the bucket "00000000-0000-0000-0000-000000000000"
    Then the cluster "my-kube" should have the feature rbac
    And the cluster "my-kube" should have the feature "network-policies"

  Scenario: I remove some features from the cluster
    Given I have the following clusters in the bucket "00000000-0000-0000-0000-000000000000":
      | identifier | type       | address         | username | password | version | features              |
      | my-kube    | kubernetes | https://1.2.3.4 | samuel   | roze     | v1.4    | rbac,network-policies |
    When I update the cluster "my-kube" of the bucket "00000000-0000-0000-0000-000000000000" with the following request:
    """
    {"features": {"rbac": true}}
    """
    And I ask the list of the clusters in the bucket "00000000-0000-0000-0000-000000000000"
    Then the cluster "my-kube" should have the feature rbac
    And the cluster "my-kube" should not have the feature "network-policies"
