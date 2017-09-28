Feature: As a developer
         I want to be able to use underscores and dashes when I provide my CP configuration

  Scenario: A service's public endpoint env var is generated correctly
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        infrastructure:
            deploy:
                cluster: foo
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
                                - name: ENDPOINT
                                  value: ${SERVICE_FOO_DASHED_PUBLIC_ENDPOINT}
    """
    When a tide is started
    And the service "foo-dashed" was created with the public address "1.2.3.4"
    And the deployment succeed
    Then the component "bar" should be deployed with the following environment variables:
      | name     | value   |
      | ENDPOINT | 1.2.3.4 |

  Scenario: A service's identifier is transformed properly when creating component
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
                    bar_underscored:
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
    Then the component "bar-underscored" should be deployed with the following environment variables:
      | name     | value   |
      | ENDPOINT | 1.2.3.4 |
