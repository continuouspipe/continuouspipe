Feature:
  In order to reduce the amount of configuration required to forward environment variables
  As a DevOps engineer
  I want to configure variables that are going to be forwarded to containers by the deploy and run tasks

  Rules:
  - Normal variables rules apply: conditions, encryption and pipeline overwrite
  - Default environment variables can be overwritten by task

  Scenario: Variable is as environment variable in deployed and ran container
    Given I have a flow with the following configuration:
    """
    variables:
    - name: SUPER_SECRET
      value: wow
    - name: SECRET
      value: qwerty
      as_environment_variable: true
    """
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    defaults:
        cluster: foo

    tasks:
        deployment:
            deploy:
                services:
                    foo:
                        specification:
                            source:
                                image: busyboxy

        tests:
            run:
                commands:
                    - echo foo
                image: busybox
    """
    When a tide is started for the branch "master"
    And the deployment succeed
    Then the component "foo" should be deployed with the following environment variables:
      | name   | value  |
      | SECRET | qwerty |
    And the component "run-tests" should be deployed with the following environment variables:
      | name   | value  |
      | SECRET | qwerty |

  Scenario: Condition is evaluated before using it for a deployed container
    Given I have a flow with the following configuration:
    """
    variables:
    - name: ENVIRONMENT
      value: prod
      as_environment_variable: true
      condition: code_reference.branch == 'master'
    - name: ENVIRONMENT
      value: dev
      as_environment_variable: true
      condition: code_reference.branch != 'master'
    """
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    defaults:
        cluster: foo

    tasks:
        deployment:
            deploy:
                services:
                    foo:
                        specification:
                            source:
                                image: busyboxy
    """
    When a tide is started for the branch "master"
    And the deployment succeed
    Then the component "foo" should be deployed with the following environment variables:
      | name        | value |
      | ENVIRONMENT | prod  |

  Scenario: Environment variable is overwritten by the tasks
    Given I have a flow with the following configuration:
    """
    variables:
    - name: ENV
      value: prod
      as_environment_variable: true
    """
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    defaults:
        cluster: foo

    tasks:
        deployment:
            deploy:
                services:
                    foo:
                        specification:
                            source:
                                image: busyboxy

        tests:
            run:
                commands:
                    - echo foo
                image: busybox
                environment_variables:
                - { name: ENV, value: dev }
    """
    When a tide is started for the branch "master"
    And the deployment succeed
    Then the component "foo" should be deployed with the following environment variables:
      | name | value |
      | ENV  | prod  |
    And the component "run-tests" should be deployed with the following environment variables:
      | name | value |
      | ENV  | dev   |

  Scenario: Overwritten variable by pipeline is overwritten in the deployed container
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    variables:
        - name: ENV
          value: prod
          as_environment_variable: true
        - name: SECRET
          value: secret
          as_environment_variable: true

    defaults:
        cluster: foo

    tasks:
        deployment:
            deploy:
                services:
                    app:
                        specification:
                            source:
                                image: busyboxy

    pipelines:
        - name: Master
          condition: code_reference.branch == 'master'
          variables:
              - name: ENV
                value: prod
          tasks:
              - deployment
    """
    When a tide is started for the branch "master"
    And the deployment succeed
    Then the component "app" should be deployed with the following environment variables:
      | name   | value  |
      | ENV    | prod   |
      | SECRET | secret |

  Scenario: Variable are among the manually defined ones
    Given I have a flow with the following configuration:
    """
    variables:
    - name: SECRET
      value: qwerty
      as_environment_variable: true
    """
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    defaults:
        cluster: foo

    tasks:
        deployment:
            deploy:
                services:
                    foo:
                        specification:
                            source:
                                image: busyboxy
                            environment_variables:
                                - { name: TEST, value: foo }

        tests:
            run:
                commands:
                    - echo foo
                image: busybox
                environment_variables:
                    - { name: TEST, value: bar }
    """
    When a tide is started for the branch "master"
    And the deployment succeed
    Then the component "foo" should be deployed with the following environment variables:
      | name   | value  |
      | SECRET | qwerty |
      | TEST   | foo    |
    And the component "run-tests" should be deployed with the following environment variables:
      | name   | value  |
      | SECRET | qwerty |
      | TEST   | bar    |

  Scenario: Variables can be exposed to only a specific set of services
    Given I have a flow with the following configuration:
    """
    variables:
    - name: SUPER_SECRET
      value: wow
      as_environment_variable: ['foo']
    - name: SECRET
      value: qwerty
      as_environment_variable: true
    """
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    defaults:
        cluster: foo

    tasks:
        deployment:
            deploy:
                services:
                    foo:
                        specification:
                            source:
                                image: busyboxy
                    bar:
                        specification:
                            source:
                                image: busyboxy

        tests:
            run:
                commands:
                    - echo foo
                image: busybox
    """
    When a tide is started for the branch "master"
    And the deployment succeed
    Then the component "foo" should be deployed with the following environment variables:
      | name         | value  |
      | SECRET       | qwerty |
      | SUPER_SECRET | wow    |
    And the component "bar" should be deployed with the following environment variables:
      | name   | value  |
      | SECRET | qwerty |
    And the component "bar" should be deployed without the following environment variables:
      | name         | value  |
      | SUPER_SECRET | foo    |
    And the component "run-tests" should be deployed with the following environment variables:
      | name   | value  |
      | SECRET | qwerty |
    And the component "run-tests" should be deployed without the following environment variables:
      | name         | value  |
      | SUPER_SECRET | wow    |
