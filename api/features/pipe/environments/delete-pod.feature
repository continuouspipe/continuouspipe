Feature:
  In order to force a container to be recreated in the event of an error
  As a user
  I want to be able to delete pods

  Background:
    Given I am authenticated
    And the bucket of the team "my-team" is the bucket "00000000-0000-0000-0000-000000000000"
    And there is a cluster in the bucket "00000000-0000-0000-0000-000000000000" with the following configuration:
      | identifier | type       | address         | version | username | password |
      | my-cluster | kubernetes | https://1.2.3.4 | v1.4    | username | password |

  Scenario: Delete a pod
    Given there is a pod "test-pod" already running
    And there is a pod "not-deleted-test-pod" already running
    When I delete the pod named "test-pod" for the team "my-team" and the cluster "my-cluster"
    Then the pod "test-pod" should be deleted
    Then the pod "not-deleted-test-pod" should not be deleted
