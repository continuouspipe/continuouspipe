Feature:
  In order to reduce efforts to setup CP
  As a developer
  I want the configuration to be populated from the continuous-pipe.yml file

  Scenario: It loads the services build context
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        images:
            build:
                services:
                    api:
                        image: sroze/my-image

    """
    And I have a "docker-compose.yml" file in my repository that contains:
    """
    api:
        build: .
    """
    When the configuration of the tide is generated
    Then the generated configuration should contain at least:
    """
    tasks:
        images:
            build:
                services:
                    api:
                        image: sroze/my-image
                        build_directory: .
    """

  Scenario: It loads the build context from docker-compose labels
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        images:
            build: ~
    """
    And I have a "docker-compose.yml" file in my repository that contains:
    """
    api:
        build: .
        labels:
            com.continuouspipe.image-name: sroze/my-image
    """
    When the configuration of the tide is generated
    Then the generated configuration should contain at least:
    """
    tasks:
        images:
            build:
                services:
                    api:
                        image: sroze/my-image
                        build_directory: .
    """

  Scenario: It shouldn't guess to build components without docker-compose.yml's `build`
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        images:
            build: ~
    """
    And I have a "docker-compose.yml" file in my repository that contains:
    """
    mysql:
        image: mysql
    """
    When the configuration of the tide is generated
    Then the generated configuration should not contain the path "[tasks][images][build][services][mysql]"

  Scenario: It loads the deployment options from the docker-compose labels
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        kube:
            deploy:
                providerName: foo
    """
    And I have a "docker-compose.yml" file in my repository that contains:
    """
    api:
        build: .
        labels:
            com.continuouspipe.image-name: sroze/my-image
            com.continuouspipe.visibility: public
        expose:
            - 80
    mysql:
        image: mysql
        labels:
            com.continuouspipe.update: lock
    """
    When the configuration of the tide is generated
    Then the generated configuration should contain at least:
    """
    tasks:
        kube:
            deploy:
                services:
                    api:
                        specification:
                            source:
                                image: sroze/my-image
                            accessibility:
                                from_external: true
                            ports:
                                - identifier: api80
                                  port: 80
                    mysql:
                        locked: true
    """

  Scenario: It loads the environment variables and replaces variable values if some
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    environment_variables:
        - name: baz
          value: bar

    tasks:
        kube:
            deploy:
                providerName: foo
    """
    And I have a "docker-compose.yml" file in my repository that contains:
    """
    container:
        image: helloworld
        environment:
          FOO: ${baz}
          EXTRA: raw
    """
    When the configuration of the tide is generated
    Then the generated configuration should contain at least:
    """
    tasks:
        kube:
            deploy:
                providerName: foo
                services:
                    container:
                        specification:
                            environment_variables:
                                - name: FOO
                                  value: bar
                                - name: EXTRA
                                  value: raw
    """
