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
                cluster: foo
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
                cluster: foo
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
                cluster: foo
                services:
                    container:
                        specification:
                            environment_variables:
                                - name: FOO
                                  value: bar
                                - name: EXTRA
                                  value: raw
    """

  Scenario: The environment variables from the `continuous-pipe.yml` file overrides the environment variables in `docker-compose.yml` file
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        kube:
            deploy:
                cluster: foo
                services:
                    container:
                        specification:
                            environment_variables:
                                - name: TWO
                                  value: two
                                - name: THREE
                                  value: three
    """
    And I have a "docker-compose.yml" file in my repository that contains:
    """
    container:
        image: helloworld
        environment:
          ONE: one
          TWO: one
    """
    When the configuration of the tide is generated
    Then the generated configuration should contain at least:
    """
    tasks:
        kube:
            deploy:
                cluster: foo
                services:
                    container:
                        specification:
                            environment_variables:
                                - name: ONE
                                  value: one
                                - name: TWO
                                  value: two
                                - name: THREE
                                  value: three
    """

  Scenario: The runtime policy should be completed with the privileged mode if there's in the `docker-compose.yml` file
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        kube:
            deploy:
                cluster: foo
    """
    And I have a "docker-compose.yml" file in my repository that contains:
    """
    api:
        image: mysql
        privileged: true
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
                                image: mysql
                            runtime_policy:
                                privileged: true
    """

  Scenario: The absolute volume mappings should be transformed as volume and volume mappings
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        kube:
            deploy:
                cluster: foo
    """
    And I have a "docker-compose.yml" file in my repository that contains:
    """
    api:
        image: mysql
        privileged: true
        volumes:
            - /var/run/docker.sock:/var/run/docker.sock
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
                                image: mysql
                            runtime_policy:
                                privileged: true
                            volumes:
                                - type: hostPath
                                  path: /var/run/docker.sock
                            volume_mounts:
                                - mount_path: /var/run/docker.sock
    """

  Scenario: The local volume mappings should not be transformed
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        kube:
            deploy:
                cluster: foo
    """
    And I have a "docker-compose.yml" file in my repository that contains:
    """
    api:
        image: mysql
        privileged: true
        volumes:
            - .:/app
    """
    When the configuration of the tide is generated
    Then the generated configuration should not contain:
    """
    tasks:
        kube:
            deploy:
                services:
                    api:
                        specification:
                            volumes:
                                - type: hostPath
                                  path: /app
                            volume_mounts:
                                - mount_path: /app
    """

  Scenario: When the image name is missing, then the tide should be failed
    Given I have a flow with the following configuration:
    """
    environment_variables:
        - { name: FOO, value: BAR }
    """
    And I have a "docker-compose.yml" file in my repository that contains:
    """
    api:
        build: .
        volumes:
            - .:/app
    """
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        images:
            build: ~

        deploy:
            deploy:
                cluster: kubernetes/cp-cluster

        installation:
            run:
                commands:
                    - echo hello
    """
    And I have a flow
    When a tide is started for the branch "configuration"
    Then the tide should be failed

  Scenario: It loads the command
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        kube:
            deploy:
                cluster: foo
    """
    And I have a "docker-compose.yml" file in my repository that contains:
    """
    api:
        image: foo/bar
        command: /app/run.sh
        expose:
            - 80
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
                                image: foo/bar
                            command:
                                - /app/run.sh
    """

  Scenario: It do not adds the other image to build
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        images:
            build:
                services:
                    service1:
                        image: sroze/my-image

    """
    And I have a "docker-compose.yml" file in my repository that contains:
    """
    service1:
        build: .
    service2:
        build: .
    """
    When the configuration of the tide is generated
    Then the generated configuration should contain at least:
    """
    tasks:
        images:
            build:
                services:
                    service1:
                        image: sroze/my-image
    """
    And the generated configuration should not contain:
    """
    tasks:
        images:
            build:
                services:
                    service2:
                        image: sroze/my-image
    """
