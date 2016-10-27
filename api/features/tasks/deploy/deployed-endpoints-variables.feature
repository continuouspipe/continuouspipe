Feature:
  In order to have complex deployment scripts
  As a developer
  I want to be able to use public endpoints deployed by the previous deployment tasks

  Scenario: If a run task is after a deployment, it should inject the public endpoint variables
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        infrastructure:
            deploy:
                cluster: foo
                services:
                    foo:
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
                                - name: ENDPOINT
                                  value: ${SERVICE_FOO_PUBLIC_ENDPOINT}
    """
    When a tide is started
    And the service "foo" was created with the public address "1.2.3.4"
    And the deployment succeed
    Then the component "bar" should be deployed with the following environment variables:
    | name     | value   |
    | ENDPOINT | 1.2.3.4 |
