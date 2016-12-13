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
