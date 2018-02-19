Feature:
  In order to have a seamless environment
  As a user
  I want CP to manage the Docker image registries for me

  Background:
    Given there is a team "sam"
    And there is a user "sam"
    And the user "sam" is "ADMIN" of the team "sam"
    And I have a flow with UUID "00000000-0000-0000-0000-000000000000" in the team "sam"

  Scenario: I can create a Docker registry for the flow
    Given I am authenticated as "sam"
    When I ask the creation of a Docker registry for the flow "00000000-0000-0000-0000-000000000000"
    Then I should be told that the resource has been created
    And a quay.io repository "flow-00000000-0000-0000-0000-000000000000" should be created
    And a quay.io robot account "project-sam" should have been created
    And the quay.io user "continuouspipe+project-sam" should have been granted access to the "continuouspipe/flow-00000000-0000-0000-0000-000000000000" repository
    And the team "sam" should have docker credentials for "quay.io/continuouspipe/flow-00000000-0000-0000-0000-000000000000" with the username "continuouspipe+project-sam"
    And the team "sam" should have docker credentials for "quay.io/continuouspipe/flow-00000000-0000-0000-0000-000000000000" with the attribute "managed" valued "true"

  Scenario: It allows to change the visibility of a managed registry
    Given I am authenticated as "sam"
    And the team "sam" have the credentials of the following Docker registry:
      | full_address                                                          | username                        | password | attributes                                       |
      | quay.io/continuouspipe-flex/flow-00000000-0000-0000-0000-000000000000 | continuouspipe-flex+project-sam | password | {"flow": "00000000-0000-0000-0000-000000000000"} |
    When I change the visibility of the registry "quay.io/continuouspipe-flex/flow-00000000-0000-0000-0000-000000000000" of the flow "00000000-0000-0000-0000-000000000000" to "private"
    Then the quay.io repository "continuouspipe-flex/flow-00000000-0000-0000-0000-000000000000" should have been changed to a private repository
    And the attribute "visibility" of the registry "quay.io/continuouspipe-flex/flow-00000000-0000-0000-0000-000000000000" of the flow "00000000-0000-0000-0000-000000000000" should have been updated with the value "private"

