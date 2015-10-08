Feature:
  In order to run commands on the built images
  As a developer
  I want to be able to say to use the built image of a given service

  Background:
    Given I have a flow

  Scenario: The image used to run the commands is the image that will be built for the service
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
                providerName: foo
                image:
                    from_service: foobar
                commands:
                    - echo hello
    """
    When the configuration of the tide is generated
    Then the generated configuration should contain at least:
    """
    tasks:
        migrations:
            run:
                image:
                    name: foo:bar
    """

  Scenario: I should be able to have multiple run tasks using the same service as source
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        images:
            build:
                services:
                    foobar:
                        image: foo
                        tag: bar

        fixtures:
            run:
                providerName: foo
                image:
                    from_service: foobar
                commands:
                    - echo hello

        migrations:
            run:
                providerName: foo
                image:
                    from_service: foobar
                commands:
                    - echo hello
    """
    When the configuration of the tide is generated
    Then the generated configuration should contain at least:
    """
    tasks:
        migrations:
            run:
                image:
                    name: foo:bar
        fixtures:
            run:
                image:
                    name: foo:bar
    """

  Scenario: I should be able to have multiple run tasks using the different services as source
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        images:
            build:
                services:
                    foobar:
                        image: foo
                        tag: bar
                    sam:
                        image: sam
                        tag: uel

        fixtures:
            run:
                providerName: foo
                image:
                    from_service: foobar
                commands:
                    - echo hello

        migrations:
            run:
                providerName: foo
                image:
                    from_service: sam
                commands:
                    - echo hello
    """
    When the configuration of the tide is generated
    Then the generated configuration should contain at least:
    """
    tasks:
        fixtures:
            run:
                image:
                    name: foo:bar
        migrations:
            run:
                image:
                    name: sam:uel
    """
