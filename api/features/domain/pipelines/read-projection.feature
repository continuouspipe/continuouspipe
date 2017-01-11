Feature:
  In order to display the pipelines
  As a system
  I want to write a read projection in Firebase

  Background:
    Given I am authenticated as "samuel"
    And the team "samuel" exists
    And the user "samuel" is "USER" of the team "samuel"
    And the head commit of branch "master" is "1234"
    And I have a flow
    And there is 1 application images in the repository

  Scenario: It creates the read model for both tides
    Given I have a "continuous-pipe.yml" file in my repository that contains:
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
    When I send a tide creation request for branch "master" and commit "1234"
    Then 2 tides should have been created
    And a tide should be wrote in Firebase under the pipeline "First pipeline"
    And a tide should be wrote in Firebase under the pipeline "Second pipeline"

  Scenario: It create the read model without any pipeline in the configuration
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        images:
            build: ~

        deployment:
            deploy:
                cluster: foo
                services: []
    """
    When I send a tide creation request for branch "master" and commit "1234"
    And a tide should be wrote in Firebase under the pipeline "Default pipeline"
