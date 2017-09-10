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
      default_as_environment_variable: true
    """
    Given I have a "continuous-pipe.yml" file in my repository that contains:
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
  Scenario: Environment variable is overwritten by the tasks
  Scenario: Overwritten variable by pipeline is overwritten in the deployed container
