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
                providerName: foo
                services: []

        testing:
            run:
                providerName: foo
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
