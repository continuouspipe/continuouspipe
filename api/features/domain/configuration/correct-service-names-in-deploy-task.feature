Feature:
  In order to avoid kubernetes errors with service identifiers
  As a user
  I want to use dashed or underscored service IDs without seeing errors

  Background:
    Given I have a flow
    And there is 1 application images in the repository

  Scenario: A service identifier with underscore is normalized
    And I have a "continuous-pipe.yml" file in my repository that contains:
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
                    app_underscored:
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
                services:
                    app-underscored:
                        specification:
                            source:
                                image: my/app
                            accessibility:
                                from_external: true
    """

  Scenario: A service identifier with uppercase letters is normalized
    And I have a "continuous-pipe.yml" file in my repository that contains:
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
                    appUppercased:
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
                services:
                    appuppercased:
                        specification:
                            source:
                                image: my/app
                            accessibility:
                                from_external: true
    """
