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

  Scenario: It can use matches in the conditions
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        first:
            run:
                cluster: foo
                image: busybox
                commands:
                    - echo first

    pipelines:
        - name: To master
          condition: code_reference.branch matches "/^cpdev/"
          tasks:
              - first
    """
    When I send a tide creation request for branch "cpdev-user" and commit "1234"
    And the tide starts
    And the task named "first" should be running

  Scenario: It creates a tide when no valid configuration is found
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        first:
            run:
                cluster: foo
                this_is_not_valid: true
    """
    When I send a tide creation request for branch "master" and commit "1234"
    Then a tide should be created
    And the tide should be failed

  Scenario: It still reads the tasks in the correct order
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
      images:
        build:
          environment:
            - name: GITHUB_TOKEN
              value: ${GITHUB_TOKEN}
          services:
            web:
              image: quay.io/inviqa_images/inviqa-teamup
              naming_strategy: sha1

      unit_test:
        run:
          cluster: ${CLUSTER}
          environment:
            name: '"${CP_ENVIRONMENT}"'
          image:
            from_service: web
          commands:
            - container composer
            - container unit_test

    pipelines:
      - name: Mainline
        condition: 'code_reference.branch in ["develop"]'
        tasks:
          - images
          - imports: unit_test
            run:
              environment:
                name: '"teamup-ci"'
              environment_variables:
                - name: DEVELOPMENT_MODE
                  value: 0
    """
    When I send a tide creation request for branch "develop" and commit "1234"
    And the tide for the branch "develop" and commit "1234" is tentatively started
    Then a tide should be created
    And the run task should be pending
    And the build task should be running
