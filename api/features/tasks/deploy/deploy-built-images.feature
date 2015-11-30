Feature:
  In order to have a coherent tide
  As a developer
  I expect the images built in this tide to be deployed

  Scenario:
    Given there is 1 application images in the repository
    When a tide is started for the branch "my-feature" with a build and deploy task
    And all the image builds are successful
    Then the deployed image tag should be "my-feature"

  Scenario:
    Given there is 1 application images in the repository
    When a tide is started with a deploy task
    Then the deployed environment name should be prefixed by the flow identifier

  Scenario: I can explicitly defines the source of a component from the built
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        images:
            build:
                services:
                    api:
                        image: foo/bar
                        build_directory: .

        deploy:
            deploy:
                cluster: foo
                services:
                    api: ~
                    worker:
                        specification:
                            source:
                                from_service: api
    """
    When the configuration of the tide is generated for the branch "my-feature"
    Then the generated configuration should contain at least:
    """
    tasks:
        images:
            build:
                services:
                    api:
                        image: foo/bar
                        tag: my-feature
        deploy:
            deploy:
                cluster: foo
                services:
                    api:
                        specification:
                            source:
                                image: foo/bar
                                tag: my-feature
                    worker:
                        specification:
                            source:
                                image: foo/bar
                                tag: my-feature
    """


  Scenario: I can explicitly defines the source of a component from the built
    Given I have a "docker-compose.yml" file in my repository that contains:
    """
    api:
        build: .
        labels:
            com.continuouspipe.image-name: foo/bar
    worker:
        build: .
    """
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        images:
            build:
                services:
                    api: ~

        deploy:
            deploy:
                cluster: foo
                services:
                    api: ~
                    worker:
                        specification:
                            source:
                                from_service: api
                            environment_variables:
                                - name: FOO
                                  value: BAR
    """
    When the configuration of the tide is generated for the branch "my-feature"
    Then the generated configuration should contain at least:
    """
    tasks:
        images:
            build:
                services:
                    api:
                        image: foo/bar
                        tag: my-feature
        deploy:
            deploy:
                cluster: foo
                services:
                    api:
                        specification:
                            source:
                                image: foo/bar
                                tag: my-feature
                    worker:
                        specification:
                            source:
                                image: foo/bar
                                tag: my-feature
    """

  Scenario:
    Given I have a "docker-compose.yml" file in my repository that contains:
    """
    api:
        build: .
        labels:
            com.continuouspipe.image-name: foo/bar
    worker:
        build: .
        labels:
            com.continuouspipe.image-name: inviqasession/cp-builder
    """
    And I have a flow with the following configuration:
    """
    tasks:
        images:
            build:
                services:
                    api: ~

        deploy:
            deploy:
                cluster: foo
                services:
                    api: ~
                    worker:
                        specification:
                            source:
                                from_service: api
                            environment_variables:
                                - name: FOO
                                  value: BAR
    """
    When a tide is started for the branch "my-feature"
    Then the configuration of the tide should contain at least:
    """
    tasks:
        images:
            build:
                services:
                    api:
                        image: foo/bar
                        tag: my-feature
        deploy:
            deploy:
                cluster: foo
                services:
                    api:
                        specification:
                            source:
                                image: foo/bar
                                tag: my-feature
                    worker:
                        specification:
                            source:
                                image: foo/bar
                                tag: my-feature
    """
