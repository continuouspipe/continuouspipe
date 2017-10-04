Feature:
  In order to force a container to be recreated in the event of an error
  As a user
  I want to be able to delete pods

  Background:
    Given there is a team "sam"
    And there is a user "sam"
    And the user "sam" is "ADMIN" of the team "sam"
    And I have a flow with UUID "00000000-0000-0000-0000-000000000000" in the team "sam"

  Scenario: Delete a pod
    Given I am authenticated as "sam"
    And there is a pod named "test-pod" in the flow "00000000-0000-0000-0000-000000000000" and the cluster "fra-01" and the namespace "test-namespace"
    When I delete the pod named "test-pod" in the flow "00000000-0000-0000-0000-000000000000" and the cluster "fra-01" and the namespace "test-namespace"
    Then the pod "test-pod" should have been deleted

  Scenario: User without authentication cannot delete a pod
    Given I am authenticated as "dave"
    And there is a pod named "test-pod" in the flow "00000000-0000-0000-0000-000000000000" and the cluster "fra-01" and the namespace "test-namespace"
    When I delete the pod named "test-pod" in the flow "00000000-0000-0000-0000-000000000000" and the cluster "fra-01" and the namespace "test-namespace"
    Then the pod "test-pod" should not have been deleted