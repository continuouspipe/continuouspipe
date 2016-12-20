Feature:
  In order to organise my tides
  As a user
  I want to be able to see and query tides in different pipelines

  Background:
    Given I am authenticated as "samuel"
    And the team "samuel" exists
    And the user "samuel" is "USER" of the team "samuel"
    And I have a flow with UUID "00000000-0000-0000-0000-000000000000"
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

        tests:
            run:
                cluster: foo
                image: busybox
                commands:
                    - echo hello

    pipelines:
        - name: To master
          condition: code_reference.branch == 'master'
          tasks:
              - images
              - deployment

        - name: Only the branches
          condition: code_reference.branch != 'master'
          tasks:
              - images
              - tests
    """

  @smoke
  Scenario: It creates the pipeline object
    When I send a tide creation request for branch "foo/bar" and commit "098789"
    And I request the flow with UUID "00000000-0000-0000-0000-000000000000"
    Then I should see the pipeline "Only the branches" in the flow

  @smoke
  Scenario: It creates the pipeline object
    When I send a tide creation request for branch "master" and commit "1234"
    And I request the flow with UUID "00000000-0000-0000-0000-000000000000"
    Then I should see the pipeline "To master" in the flow
