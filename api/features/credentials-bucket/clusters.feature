Feature:
  In order to deploy the applications
  As a administrator
  I need to manage clusters to which the developers can deploy to

  Background:
    Given I am authenticated as user "samuel"
    And the user "samuel" have access to the bucket "00000000-0000-0000-0000-000000000000"

  Scenario: Create a new Kubernetes cluster
    When I create a cluster with the following configuration in the bucket "00000000-0000-0000-0000-000000000000":
      | identifier | type       | address         | username | password | version |
      | my-kube    | kubernetes | https://1.2.3.4 | username | password | v1.4    |
    Then the new cluster should have been saved successfully

  Scenario: Cannot create another cluster with the same identifier
    Given I have the following clusters in the bucket "00000000-0000-0000-0000-000000000000":
      | identifier | type       | address         | username | password | version |
      | my-kube    | kubernetes | https://1.2.3.4 | samuel   | roze     | v1.4    |
    When I create a cluster with the following configuration in the bucket "00000000-0000-0000-0000-000000000000":
      | identifier | type       | address         | username | password | version |
      | my-kube    | kubernetes | https://2.3.4.5 | username | password | v1.4.3  |
    Then the new cluster should not have been saved successfully

  @smoke
  Scenario: List clusters of bucket
    Given I have the following clusters in the bucket "00000000-0000-0000-0000-000000000000":
      | identifier | type       | address         | username | password | version |
      | my-kube    | kubernetes | https://1.2.3.4 | samuel   | roze     | v1.4    |
    When I ask the list of the clusters in the bucket "00000000-0000-0000-0000-000000000000"
    Then I should receive a list
    And the list should contain the cluster "my-kube"

  Scenario: Delete a cluster
    Given I have the following clusters in the bucket "00000000-0000-0000-0000-000000000000":
      | identifier | type       | address         | username | password | version |
      | my-kybe    | kubernetes | https://1.2.3.4 | samuel   | roze     | v1.4    |
    When I delete the cluster "my-kube" from the bucket "00000000-0000-0000-0000-000000000000"
    And I ask the list of the clusters in the bucket "00000000-0000-0000-0000-000000000000"
    Then the list should not contain the cluster "my-kube"
