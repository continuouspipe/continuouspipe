Feature:
  In order to deploy the applications
  As a administrator
  I need to manage clusters to which the developers can deploy to

  Background:
    Given the user "samuel" have access to the bucket "00000000-0000-0000-0000-000000000000"

  Scenario: Create a new Kubernetes cluster
    Given I am authenticated as user "samuel"
    When I create a cluster with the following configuration in the bucket "00000000-0000-0000-0000-000000000000":
      | identifier | type       | address         | username | password | version |
      | my-kube    | kubernetes | https://1.2.3.4 | username | password | v1.4    |
    Then the new cluster should have been saved successfully

  Scenario: Cannot create another cluster with the same identifier
    Given I am authenticated as user "samuel"
    And I have the following clusters in the bucket "00000000-0000-0000-0000-000000000000":
      | identifier | type       | address         | username | password | version |
      | my-kube    | kubernetes | https://1.2.3.4 | samuel   | roze     | v1.4    |
    When I create a cluster with the following configuration in the bucket "00000000-0000-0000-0000-000000000000":
      | identifier | type       | address         | username | password | version |
      | my-kube    | kubernetes | https://2.3.4.5 | username | password | v1.4.3  |
    Then the new cluster should not have been saved successfully

  Scenario: Creating a new Kubernetes cluster is allowed with path in the address
    Given I am authenticated as user "samuel"
    When I create a cluster with the following configuration in the bucket "00000000-0000-0000-0000-000000000000":
      | identifier | type       | address             | username | password | version |
      | my-kube    | kubernetes | https://1.2.3.4/foo | username | password | v1.4    |
    Then the new cluster should have been saved successfully

  Scenario: Cannot create Kubernetes cluster when address is invalid
    Given I am authenticated as user "samuel"
    When I create a cluster with the following configuration in the bucket "00000000-0000-0000-0000-000000000000":
      | identifier | type       | address         | username | password | version |
      | my-kube    | kubernetes | https://        | username | password | v1.4    |
    Then the new cluster should not have been saved successfully

  Scenario: Cannot create Kubernetes cluster when address is invalid
    Given I am authenticated as user "samuel"
    When I create a cluster with the following configuration in the bucket "00000000-0000-0000-0000-000000000000":
      | identifier | type       | address | username | password | version |
      | my-kube    | kubernetes | foo.com | username | password | v1.4    |
    Then the new cluster should not have been saved successfully

  Scenario: Cannot create Kubernetes cluster when address URL scheme is not HTTP or HTTPS
    Given I am authenticated as user "samuel"
    When I create a cluster with the following configuration in the bucket "00000000-0000-0000-0000-000000000000":
      | identifier | type       | address           | username | password | version |
      | my-kube    | kubernetes | ftp://invalid.com | username | password | v1.4    |
    Then the new cluster should not have been saved successfully

  @smoke
  Scenario: List clusters of bucket
    Given I am authenticated as user "samuel"
    And I have the following clusters in the bucket "00000000-0000-0000-0000-000000000000":
      | identifier | type       | address         | username | password | version |
      | my-kube    | kubernetes | https://1.2.3.4 | samuel   | roze     | v1.4    |
    When I ask the list of the clusters in the bucket "00000000-0000-0000-0000-000000000000"
    Then I should receive a list
    And the list should contain the cluster "my-kube"

  Scenario: Delete a cluster
    Given I am authenticated as user "samuel"
    And I have the following clusters in the bucket "00000000-0000-0000-0000-000000000000":
      | identifier | type       | address         | username | password | version |
      | my-kube    | kubernetes | https://1.2.3.4 | samuel   | roze     | v1.4    |
    When I delete the cluster "my-kube" from the bucket "00000000-0000-0000-0000-000000000000"
    And I ask the list of the clusters in the bucket "00000000-0000-0000-0000-000000000000"
    Then the list should not contain the cluster "my-kube"

  Scenario: As a system user, I can add custers
    Given there is the system api key "1234"
    When I create a cluster with the following configuration in the bucket "00000000-0000-0000-0000-000000000000" with the API key "1234":
      | identifier | type       | address         | username | password | version |
      | my-kube    | kubernetes | https://1.2.3.4 | username | password | v1.4    |
    Then the new cluster should have been saved successfully

  Scenario: Create a cluster with a client certificate
    Given I am authenticated as user "samuel"
    When I create a cluster with the following configuration in the bucket "00000000-0000-0000-0000-000000000000":
      | identifier | type       | address             | username | client_certificate | version |
      | my-kube    | kubernetes | https://1.2.3.4/foo | username | [this is long...]  | v1.6    |
    Then the new cluster should have been saved successfully
    And I ask the list of the clusters in the bucket "00000000-0000-0000-0000-000000000000"
    And the cluster "my-kube" should have a client certificate

  Scenario: Create a cluster with a CA certificate
    Given I am authenticated as user "samuel"
    When I create a cluster with the following configuration in the bucket "00000000-0000-0000-0000-000000000000":
      | identifier | type       | address             | username | client_certificate | ca_certificate     | version |
      | my-kube    | kubernetes | https://1.2.3.4/foo | username | [this is long...]  | [this is long...]  | v1.6    |
    Then the new cluster should have been saved successfully
    And I ask the list of the clusters in the bucket "00000000-0000-0000-0000-000000000000"
    And the cluster "my-kube" should have a CA certificate

  Scenario: Create a cluster with a Google Cloud Service account
    Given I am authenticated as user "samuel"
    When I create a cluster with the following configuration in the bucket "00000000-0000-0000-0000-000000000000":
      | identifier | type       | address             | username | google_cloud_service_account | version |
      | my-kube    | kubernetes | https://1.2.3.4/foo | username | [this is long...]            | v1.6    |
    Then the new cluster should have been saved successfully
    And I ask the list of the clusters in the bucket "00000000-0000-0000-0000-000000000000"
    And the cluster "my-kube" should have a Google Cloud service account

  @smoke
  Scenario: Create a cluster with management credentials as well
    Given I am authenticated as user "samuel"
    When I create a cluster with the following configuration in the bucket "00000000-0000-0000-0000-000000000000":
      | identifier | type       | address             | username | google_cloud_service_account | management_credentials                         | version |
      | my-kube    | kubernetes | https://1.2.3.4/foo | username | [this is long...]            | {"google_cloud_service_account": "base64..."} | v1.6    |
    Then the new cluster should have been saved successfully
    And I ask the list of the clusters in the bucket "00000000-0000-0000-0000-000000000000"
    And the cluster "my-kube" should have a Google Cloud service account
    And the cluster "my-kube" should have management credentials
    And the cluster "my-kube" should have a Google Cloud service account for its management credentials
