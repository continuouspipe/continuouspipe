Feature:
  In order to configure run commands on the built images
  As a developer
  I want to be able to pass in environment variables

  Scenario: environment variables can be passed in as a hash
    Given I have a flow
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        images:
            build:
                services:
                    foobar:
                        image: foo
                        tag: bar

        migrations:
            run:
                cluster: foo
                image:
                    from_service: foobar
                commands:
                    - echo hello
                environment_variables:
                    BAR: BAZ
    """
    When the configuration of the tide is generated
    Then the generated configuration should contain at least:
    """
    tasks:
        migrations:
            run:
                environment_variables:
                    BAR:
                      name: BAR
                      value: BAZ
    """