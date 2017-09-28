Feature:
  In order to have the full DNS of services running in the same namespace or from another environment
  As a DevOps engineer
  I want to be able to use a variable to know the environment name

  Scenario: Uses the variable to know the current environment name
    Given I have a flow with UUID "00000000-0000-0000-0000-000000000000"
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        application:
            deploy:
                cluster: foo
                services:
                    foo:
                        specification:
                            source:
                                image: bar

                            environment_variables:
                                - name: ENVIRONMENT
                                  value: ${__TASK_APPLICATION_TARGET_ENVIRONMENT_NAME}
    """
    When a tide is started for the branch "master"
    Then the component "foo" should be deployed with the following environment variables:
      | name        | value                                       |
      | ENVIRONMENT | 00000000-0000-0000-0000-000000000000-master |

  Scenario: Uses the variable to know about another task's environment
    Given I have a flow with UUID "00000000-0000-0000-0000-000000000000"
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        infrastructure:
            deploy:
                cluster: foo
                environment:
                    name: '"foo-" ~ code_reference.branch'
                services:
                    foo-dashed:
                        specification:
                            source:
                                image: foo

        application:
            deploy:
                cluster: foo
                services:
                    bar:
                        specification:
                            source:
                                image: bar

                            environment_variables:
                                - name: ENVIRONMENT
                                  value: ${__TASK_INFRASTRUCTURE_TARGET_ENVIRONMENT_NAME}
    """
    When a tide is started for the branch "master"
    And the deployment succeed
    Then the component "bar" should be deployed with the following environment variables:
      | name        | value      |
      | ENVIRONMENT | foo-master |

  Scenario: It works with task name with dashes and replaces them with underscores
    Given I have a flow with UUID "00000000-0000-0000-0000-000000000000"
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        application-deployment:
            deploy:
                cluster: foo
                services:
                    foo:
                        specification:
                            source:
                                image: bar

                            environment_variables:
                                - name: ENVIRONMENT
                                  value: ${__TASK_APPLICATION_DEPLOYMENT_TARGET_ENVIRONMENT_NAME}
    """
    When a tide is started for the branch "master"
    Then the component "foo" should be deployed with the following environment variables:
      | name        | value                                       |
      | ENVIRONMENT | 00000000-0000-0000-0000-000000000000-master |

  Scenario: It works by overriding variables inside variables
    Given I have a flow with UUID "00000000-0000-0000-0000-000000000000"
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    variables:
        SMTP_HOST: mailcatcher.${__TASK_APPLICATION_TARGET_ENVIRONMENT_NAME}.svc.cluster.local

    tasks:
        application:
            deploy:
                cluster: foo
                services:
                    foo:
                        specification:
                            source:
                                image: bar

                            environment_variables:
                                - name: ENVIRONMENT
                                  value: ${SMTP_HOST}
    """
    When a tide is started for the branch "master"
    Then the component "foo" should be deployed with the following environment variables:
      | name        | value                                                                     |
      | ENVIRONMENT | mailcatcher.00000000-0000-0000-0000-000000000000-master.svc.cluster.local |

  Scenario: The environment name is normalized as it would be normally
    Given I have a flow with UUID "00000000-0000-0000-0000-000000000000"
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        application:
            deploy:
                cluster: foo
                services:
                    foo:
                        specification:
                            source:
                                image: bar

                            environment_variables:
                                - name: ENVIRONMENT
                                  value: ${__TASK_APPLICATION_TARGET_ENVIRONMENT_NAME}
    """
    When a tide is started for the branch "this-long-branch-name-will-force-continuous-pipe-to-truncate-hash-and-do-its-magic-for-it-to-work"
    Then the component "foo" should be deployed with the following environment variables:
      | name        | value                                                           |
      | ENVIRONMENT | 00000000-0000-0000-0000-000000000000-this-long-branc-8ef2133d04 |
