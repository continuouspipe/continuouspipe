Feature:
  In order to reduce the misconfiguration risks
  As a flow configurator
  I want to see which variables are missing from my configuration file

  Background:
    Given I am authenticated as "samuel"
    Given there is a team "samuel"
    And the user "samuel" is "ADMIM" of the team "samuel"

  Scenario: A non-defined variable is missing
    Given I have a flow
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        kubernetes:
            deploy:
                cluster: ${CLUSTER}
                services: []
    """
    When I request the flow configuration
    Then the variable "CLUSTER" should be missing

  Scenario: An already defined variable in the continuous-pipe.yml file should be not missing
    Given I have a flow
    And  I have a "continuous-pipe.yml" file in my repository that contains:
    """
    variables:
        - name: CLUSTER
          value: FOO

    tasks:
        kubernetes:
            deploy:
                cluster: ${CLUSTER}
                services: []
    """
    When I request the flow configuration
    Then the variable "CLUSTER" should not be missing

  Scenario: An already defined variable in the flow configuration should be not missing
    Given I have a flow with the following configuration:
    """
    variables:
        - name: CLUSTER
          value: FOO
    """
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        kubernetes:
            deploy:
                cluster: ${CLUSTER}
                services: []
    """
    When I request the flow configuration
    Then the variable "CLUSTER" should not be missing

  Scenario: The escaped variables are not missing
    Given I have a flow
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        kubernetes:
            deploy:
                cluster: \${FOO}
                services: []
    """
    When I request the flow configuration
    Then the variable "FOO" should not be missing

  Scenario: Dynamic variables are not missing
    Given I have a flow
    And I have a "continuous-pipe.yml" file in my repository that contains:
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
                                - name: ENDPOINT2
                                  value: ${ENDPOINT_HTTPS_API_PUBLIC_ENDPOINT}
    """
    When I request the flow configuration
    Then the variable "SERVICE_FOO_PUBLIC_ENDPOINT" should not be missing
    And the variable "ENDPOINT_HTTPS_API_PUBLIC_ENDPOINT" should not be missing
