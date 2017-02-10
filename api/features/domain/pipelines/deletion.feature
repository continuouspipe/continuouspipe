Feature:
  In order to free up resources
  As a user
  I want to be able to delete different pipelines

  Background:
    Given I am authenticated as "samuel"
    And the team "samuel" exists
    And I have a flow with UUID "00000000-0000-0000-0000-000000000000" in the team "samuel"
    And there is 1 application images in the repository
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        images:
            build: ~

        deployment:
            deploy:
                cluster: foo
                services: []

    pipelines:
        - name: First pipeline
          tasks:
              - images
              - deployment

        - name: Second pipeline
          tasks:
              - images
    """

  Scenario: Deleting the selected pipeline of a flow
    Given the user "samuel" is "ADMIN" of the team "samuel"
    And I send a tide creation request for branch "master" and commit "1234"
    When I send a pipeline deletion request for flow "00000000-0000-0000-0000-000000000000" and pipeline "First pipeline"
    And the pipeline is successfully removed
    And I request the flow
    Then I should not see the pipeline "First pipeline" in the flow
    And I should see the pipeline "Second pipeline" in the flow
    And the pipeline "First pipeline" in flow "00000000-0000-0000-0000-000000000000" should be deleted from the permanent storage of views

  Scenario: Cannot delete the selected pipeline without administrator permission
    Given the user "samuel" is "USER" of the team "samuel"
    And I send a tide creation request for branch "master" and commit "1234"
    When I send a pipeline deletion request for flow "00000000-0000-0000-0000-000000000000" and pipeline "First pipeline"
    Then I should be told that I don't have the permissions
