Feature:
  In order to have a seamless environment
  As a user
  I want CP to manage the Docker image registries for me

  Background:
    Given there is a team "sam"
    And there is a user "sam"
    And the user "sam" is "ADMIN" of the team "sam"
    And I have a flow with UUID "00000000-0000-0000-0000-000000000000" in the team "sam"
    And I am authenticated as "sam"

  Scenario: It will create a Docker Registry automatically
    When I activate flex for the flow "00000000-0000-0000-0000-000000000000"
    Then a quay.io repository "flow-00000000-0000-0000-0000-000000000000" should be created
    And a quay.io robot account "project-sam" should have been created
    And the quay.io user "continuouspipe-flex+project-sam" should have been granted access to the "continuouspipe-flex/flow-00000000-0000-0000-0000-000000000000" repository
    And the team "sam" should have docker credentials for "quay.io" with the username "continuouspipe-flex+project-sam"
