Feature:
  In order to simplify the configuration file
  As a user
  I want to be able to set default values for tasks

  Background:
    Given I have a flow
    And there is 1 application images in the repository

  Scenario: Set default environment names on deploy and run tasks
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    defaults:
        environment:
            name: '"river-" ~ code_reference.branch'

    tasks:
        images:
            build: ~

        first:
            deploy:
                cluster: foo
                services:
                    app:
                        specification:
                            source:
                                image: my/app
                            accessibility:
                                from_external: true
                            ports:
                                - 80

        second:
            run:
                cluster: foo
                image: busybox
                commands:
                    - echo hello
    """
    When the configuration of the tide is generated
    Then the generated configuration should contain at least:
    """
    tasks:
        first:
            deploy:
                environment:
                    name: '"river-" ~ code_reference.branch'
        second:
            run:
                environment:
                    name: '"river-" ~ code_reference.branch'
    """
    And the generated configuration should not contain:
    """
    tasks:
        images:
            build:
                environment:
                    name: '"river-" ~ code_reference.branch'
    """

  Scenario: The default values are not overriding the specific values
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    defaults:
        environment:
            name: '"foo-" ~ code_reference.branch'

    tasks:
        first:
            deploy:
                cluster: foo
                services:
                    app:
                        specification:
                            source:
                                image: my/app
                            accessibility:
                                from_external: true
                            ports:
                                - 80

        second:
            run:
                cluster: foo
                environment:
                    name: '"bar-" ~ code_reference.branch'
                image: busybox
                commands:
                    - echo hello
    """
    When the configuration of the tide is generated
    Then the generated configuration should contain at least:
    """
    tasks:
        first:
            deploy:
                environment:
                    name: '"foo-" ~ code_reference.branch'
        second:
            run:
                environment:
                    name: '"bar-" ~ code_reference.branch'
    """
