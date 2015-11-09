Feature:
  In order to run setup scripts
  As a developer
  I want to be able to use the river's public endpoint environs in the run commands environs

  Scenario: If a run task is after a deployment, it should inject the public endpoint variables
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        deploy:
            deploy:
                cluster: foo
                services: []

        testing:
            run:
                cluster: foo
                commands:
                    - echo foo
                image: busybox
    """
    When a tide is started
    And the service "foo" was created with the public address "1.2.3.4"
    And the deployment succeed
    Then the commands should be run with the following environment variables:
    | name                        | value   |
    | SERVICE_FOO_PUBLIC_ENDPOINT | 1.2.3.4 |

  Scenario: The variables with the deployed component references are replaced before run
    Given I have a flow with the following configuration:
    """
    environment_variables:
        - { name: FOO, value: BAR }
    """
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        deploy:
            deploy:
                cluster: foo
                services: []

        testing:
            run:
                cluster: foo
                commands:
                    - echo foo
                image: busybox
                environment:
                    - name: BAZ
                      value: ${FOO}
                    - name: PUBLIC_ADDRESS
                      value: ${SERVICE_BAR_PUBLIC_ENDPOINT}
    """
    When a tide is started
    And the service "bar" was created with the public address "1.2.3.4"
    And the deployment succeed
    Then the commands should be run with the following environment variables:
      | name                        | value   |
      | BAZ                         | BAR     |
      | SERVICE_BAR_PUBLIC_ENDPOINT | 1.2.3.4 |
      | PUBLIC_ADDRESS              | 1.2.3.4 |
