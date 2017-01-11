Feature:
  In order to logically separate the tides
  As a user
  I want to be able to create different pipelines

  Background:
    Given I am authenticated as "samuel"
    And the team "samuel" exists
    And the user "samuel" is "USER" of the team "samuel"
    And the head commit of branch "master" is "1234"
    And I have a flow
    And there is 1 application images in the repository

  Scenario: It creates the tide from the according pipeline
    Given I have a "continuous-pipe.yml" file in my repository that contains:
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
    When I send a tide creation request for branch "master" and commit "1234"
    Then a tide should be created
    And the tide should have the task "images"
    And the tide should have the task "deployment"
    And the tide should not have the task "tests"

  Scenario: It creates the tide from the according pipeline
    Given I have a "continuous-pipe.yml" file in my repository that contains:
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
    When I send a tide creation request for branch "foo/bar" and commit "1234"
    Then a tide should be created
    And the tide should have the task "images"
    And the tide should have the task "tests"
    And the tide should not have the task "deployment"

  Scenario: It can creates many tides for a given request
    Given I have a "continuous-pipe.yml" file in my repository that contains:
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
          tasks:
              - images
              - deployment

        - name: Only the branches
          tasks:
              - images
              - tests
    """
    When I send a tide creation request for branch "foo/bar" and commit "1234"
    Then 2 tides should have been created

  Scenario: It allows nothing to be run
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
        - name: To master
          condition: code_reference.branch == 'production'
          tasks:
              - images
              - deployment
    """
    When I send a tide creation request for branch "foo/bar" and commit "1234"
    Then 0 tides should have been created

  Scenario: It uses the pipeline from events coming from the code repository too
    Given I have a "continuous-pipe.yml" file in my repository that contains:
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
    """
    When the commit "12345" is pushed to the branch "master"
    Then the tide should be created
    And the tide should have the task "images"
    And the tide should have the task "deployment"
    And the tide should not have the task "tests"

  Scenario: It should keep the tasks' filter
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        first:
            filter:
                expression: code_reference.branch != 'master'

            run:
                cluster: foo
                image: busybox
                commands:
                    - echo first

        second:
            run:
                cluster: foo
                image: busybox
                commands:
                    - echo second

    pipelines:
        - name: To master
          condition: code_reference.branch == 'master'
          tasks:
              - first
              - second
    """
    When I send a tide creation request for branch "master" and commit "1234"
    And the tide starts
    Then the task named "first" should be skipped
    And the task named "second" should be running
