Feature:
  In order to have ContinuousPipe using default or enforcing use of some features
  As an Ops Engineer
  I want to set policies on the cluster, that will apply on all the environments deployed on this cluster

  Background:
    Given the user "samuel" have access to the bucket "00000000-0000-0000-0000-000000000000"
    And I am authenticated as user "samuel"

  Scenario: I create a cluster with the policies
    Given I have the following clusters in the bucket "00000000-0000-0000-0000-000000000000":
      | identifier | type       | address         | username | password | version | policies          |
      | my-kube    | kubernetes | https://1.2.3.4 | samuel   | roze     | v1.4    | [{"name":"rbac"}] |
    When I ask the list of the clusters in the bucket "00000000-0000-0000-0000-000000000000"
    Then the cluster "my-kube" should have the policy rbac
    And the cluster "my-kube" should not have the policy "network-policies"

  @smoke
  Scenario: I update the cluster's policies
    Given I have the following clusters in the bucket "00000000-0000-0000-0000-000000000000":
      | identifier | type       | address         | username | password | version |
      | my-kube    | kubernetes | https://1.2.3.4 | samuel   | roze     | v1.4    |
    When I update the cluster "my-kube" of the bucket "00000000-0000-0000-0000-000000000000" with the following request:
    """
    {"policies": [{"name": "rbac"}, {"name": "network-policies"}]}
    """
    And I ask the list of the clusters in the bucket "00000000-0000-0000-0000-000000000000"
    Then the cluster "my-kube" should have the policy rbac
    And the cluster "my-kube" should have the policy "network-policies"

  Scenario: I cannot update the cluster's policies of a managed cluster
    Given I have the following clusters in the bucket "00000000-0000-0000-0000-000000000000":
      | identifier | type       | address         | username | password | version | policies              |
      | my-kube    | kubernetes | https://1.2.3.4 | samuel   | roze     | v1.4    | [{"name": "managed"}] |
    When I update the cluster "my-kube" of the bucket "00000000-0000-0000-0000-000000000000" with the following request:
    """
    {"policies": [{"name": "rbac"}, {"name": "network-policies"}]}
    """
    Then I should be told that I don't have the authorization for this

  Scenario: I remove some policies from the cluster
    Given I have the following clusters in the bucket "00000000-0000-0000-0000-000000000000":
      | identifier | type       | address         | username | password | version | policies                                         |
      | my-kube    | kubernetes | https://1.2.3.4 | samuel   | roze     | v1.4    | [{"name": "rbac"}, {"name": "network-policies"}] |
    When I update the cluster "my-kube" of the bucket "00000000-0000-0000-0000-000000000000" with the following request:
    """
    {"policies": [{"name": "rbac"}]}
    """
    And I ask the list of the clusters in the bucket "00000000-0000-0000-0000-000000000000"
    Then the cluster "my-kube" should have the policy rbac
    And the cluster "my-kube" should not have the policy "network-policies"

  Scenario: Policies have a configuration
    Given I have the following clusters in the bucket "00000000-0000-0000-0000-000000000000":
      | identifier | type       | address         | username | password | version |
      | my-kube    | kubernetes | https://1.2.3.4 | samuel   | roze     | v1.4    |
    When I update the cluster "my-kube" of the bucket "00000000-0000-0000-0000-000000000000" with the following request:
    """
    {"policies": [{"name": "ingress", "configuration": {"type": "nginx"}}]}
    """
    And I ask the list of the clusters in the bucket "00000000-0000-0000-0000-000000000000"
    Then the cluster "my-kube" should have the policy ingress with the following configuration:
    """
    {"type": "nginx"}
    """

  Scenario: Policies have secret not accessible by users
    Given I have the following clusters in the bucket "00000000-0000-0000-0000-000000000000":
      | identifier | type       | address         | username | password | version |
      | my-kube    | kubernetes | https://1.2.3.4 | samuel   | roze     | v1.4    |
    When I update the cluster "my-kube" of the bucket "00000000-0000-0000-0000-000000000000" with the following request:
    """
    {"policies": [{"name": "cloud-flare", "configuration": {"proxied": "true"}, "secrets": {"api_key": "1234"}}]}
    """
    And I ask the list of the clusters in the bucket "00000000-0000-0000-0000-000000000000"
    Then the cluster "my-kube" should have the policy "cloud-flare" with the following configuration:
    """
    {"proxied": "true"}
    """
    And the cluster "my-kube" should have the policy "cloud-flare" with the following secrets:
    """
    {"api_key": "OBFUSCATED"}
    """

  Scenario: Policies have secret accessible by system users
    Given there is the system api key "1234567890"
    And I have the following clusters in the bucket "00000000-0000-0000-0000-000000000000":
      | identifier | type       | address         | username | password | version |
      | my-kube    | kubernetes | https://1.2.3.4 | samuel   | roze     | v1.4    |
    When I update the cluster "my-kube" of the bucket "00000000-0000-0000-0000-000000000000" with the following request:
    """
    {"policies": [{"name": "cloud-flare", "configuration": {"proxied": "true"}, "secrets": {"api_key": "1234"}}]}
    """
    And I am not authenticated
    And I ask the list of the clusters in the bucket "00000000-0000-0000-0000-000000000000" with the API key "1234567890"
    Then the cluster "my-kube" should have the policy "cloud-flare" with the following configuration:
    """
    {"proxied": "true"}
    """
    And the cluster "my-kube" should have the policy "cloud-flare" with the following secrets:
    """
    {"api_key": "1234"}
    """
