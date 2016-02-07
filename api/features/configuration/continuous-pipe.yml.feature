Feature:
  In order to have a granular configuration
  As a developer
  In want to be able to store my configuration under the same way both in a file in the repository and on CP side

  Scenario: The configuration is loaded from the file stored in the repository
    Given there is 1 application images in the repository
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        - build: ~
    """
    When a tide is started
    Then the build task should be running

  Scenario: When the deploy task is missing some arguments, then the tide should be failed
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        kubernetes:
            deploy: ~
    """
    When a tide is started
    Then the tide should be failed

  Scenario: The configuration should be merged with the one stored on CP's side
    Given I have a flow with the following configuration:
    """
    tasks:
        kubernetes:
            deploy:
                cluster: foo/bar
                services: []
    """
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        images:
            build:
                services: []
    """
    When a tide is created
    Then the configuration of the tide should contain at least:
    """
    tasks:
        images:
            build: {}

        kubernetes:
            deploy:
                cluster: foo/bar
    """

  Scenario: The configuration in the repository file is more important
    Given I have a flow with the following configuration:
    """
    tasks:
        named:
            deploy:
                cluster: foo/bar
    """
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        named:
            deploy:
                cluster: bar/baz
                services: []
    """
    When a tide is created
    Then the configuration of the tide should contain at least:
    """
    tasks:
        named:
            deploy:
                cluster: bar/baz
    """

  Scenario: The configuration can be fully configured on the CP's side
    Given there is 1 application images in the repository
    Given I have a flow with the following configuration:
    """
    tasks:
        foo:
            build: ~
    """
    When a tide is started
    Then the build task should be running

  Scenario: The variables are replaced in the configuration
    Given I have a flow with the following configuration:
    """
    environment_variables:
        - name: FOO
          value: BAR
    """
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        named:
            deploy:
                cluster: ${FOO}
                services: []
    """
    When a tide is created
    Then the configuration of the tide should contain at least:
    """
    tasks:
        named:
            deploy:
                cluster: BAR
    """

  Scenario: The configuration is not valid if many task configuration are in the same task
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        - deploy:
              cluster: foo
              services: []
          build:
              services: []
    """
    When a tide is started
    Then the tide should be failed

  Scenario: The YAML file is not valid
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        foo: 1234
            bar: baz
    """
    When a tide is started
    Then the tide should be failed
