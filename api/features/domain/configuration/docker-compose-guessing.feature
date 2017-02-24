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
                        deployment_strategy:
                            locked: true
    """

  Scenario: It loads the environment variables and replaces variable values if some
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    variables:
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
    variables:
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

  Scenario: It can build only one image but deploy it on two services
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        images:
            build:
                services:
                    service1:
                        image: sroze/my-image
        kube:
            deploy:
                cluster: foo
                services:
                    service2:
                        specification:
                            source:
                                image: sroze/my-image
                    service1:
                        specification:
                            source:
                                image: sroze/my-image

    """
    And I have a "docker-compose.yml" file in my repository that contains:
    """
    service1:
        build: .
        command: /app/api.sh
    service2:
        build: .
        command: /app/worker.sh
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
        kube:
            deploy:
                cluster: foo
                services:
                    service2:
                        specification:
                            source:
                                image: sroze/my-image
                            command: [ /app/worker.sh ]
                    service1:
                        specification:
                            source:
                                image: sroze/my-image
                            command: [ /app/api.sh ]
    """

  Scenario: It fills all the deploy tasks
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        first:
            deploy:
                cluster: foo
        second:
            deploy:
                cluster: foo
                services:
                    api: ~
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
        expose:
            - 3306
    """
    When the configuration of the tide is generated
    Then the generated configuration should contain at least:
    """
    tasks:
        first:
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
        second:
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
    """

  Scenario: It shortens long service names in port identifier
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        first:
            deploy:
                cluster: foo
        second:
            deploy:
                cluster: foo
                services:
                    elasticsearch: ~
    """
    And I have a "docker-compose.yml" file in my repository that contains:
    """
    elasticsearch:
        image: elasticsearch
        expose:
            - 9200
    """
    When the configuration of the tide is generated
    Then the generated configuration should contain at least:
    """
    tasks:
        first:
            deploy:
                services:
                    elasticsearch:
                        specification:
                            ports:
                                - identifier: elasticsear9200
                                  port: 9200
        second:
            deploy:
                services:
                    elasticsearch:
                        specification:
                            ports:
                                - identifier: elasticsear9200
                                  port: 9200
    """

  Scenario: It loads everything from the v2 of Docker Compose
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        kube:
            deploy:
                cluster: foo
    """
    And I have a "docker-compose.yml" file in my repository that contains:
    """
    version: '2'
    services:
        api:
            image: foo/bar
            command: /app/run.sh
            expose:
                - 80
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
                                image: foo/bar
                            command:
                                - /app/run.sh
                            volumes:
                                - type: hostPath
                                  path: /var/run/docker.sock
                            volume_mounts:
                                - mount_path: /var/run/docker.sock
    """

  Scenario: It loads the build directory and Docker file from docker-compose
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        images:
            build:
                services:
                    api:
                        image: docker.io/sroze/api
    """
    And I have a "docker-compose.yml" file in my repository that contains:
    """
    version: '2'
    services:
        api:
            build:
                context: .
                dockerfile: docker/style-guide/Dockerfile
    """
    When the configuration of the tide is generated
    Then the generated configuration should contain at least:
    """
    tasks:
        images:
            build:
                services:
                    api:
                        image: docker.io/sroze/api
                        build_directory: .
                        docker_file_path: docker/style-guide/Dockerfile
    """

  Scenario: The commands in CP's configuration overrides the ones in docker-compose.yml
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        kube:
            deploy:
                cluster: foo
                services:
                    service1:
                        specification:
                            source:
                                image: sroze/my-image
                            command:
                                - node
                                - /app/worker.js
    """
    And I have a "docker-compose.yml" file in my repository that contains:
    """
    service1:
        build: .
        command: /app/api.sh
    """
    When the configuration of the tide is generated
    Then the generated configuration should contain at least:
    """
    tasks:
        kube:
            deploy:
                cluster: foo
                services:
                    service1:
                        specification:
                            source:
                                image: sroze/my-image
                            command: [ node, /app/worker.js ]
    """

  Scenario: It fills all the deploy tasks
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        first:
            deploy:
                cluster: foo
                services:
                    api:
                        specification:
                            ports:
                                - 80
    """
    And I have a "docker-compose.yml" file in my repository that contains:
    """
    api:
        build: .
        labels:
            com.continuouspipe.image-name: sroze/my-image
            com.continuouspipe.visibility: public
        expose:
            - 8080
    """
    When the configuration of the tide is generated
    Then the generated configuration should contain at least:
    """
    tasks:
        first:
            deploy:
                services:
                    api:
                        specification:
                            ports:
                                - port: 80
    """

  Scenario: The Docker-Compose commands are used
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        kube:
            deploy:
                cluster: foo
                services:
                    service1:
                        specification:
                            source:
                                image: sroze/my-image
    """
    And I have a "docker-compose.yml" file in my repository that contains:
    """
    service1:
        build: .
        command: [node, "/app/api.js"]
    """
    When the configuration of the tide is generated
    Then the generated configuration should contain at least:
    """
    tasks:
        kube:
            deploy:
                cluster: foo
                services:
                    service1:
                        specification:
                            source:
                                image: sroze/my-image
                            command: [ node, /app/api.js ]
    """

  Scenario: CP's image have the priority
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        images:
            build:
                services:
                    api:
                        image: sroze/my-image

        kube:
            deploy:
                cluster: foo
                services:
                    redis:
                        specification:
                            source:
                                image: sroze/my-image
    """
    And I have a "docker-compose.yml" file in my repository that contains:
    """
    api:
        image: foo

    redis:
        image: redis
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

        kube:
            deploy:
                cluster: foo
                services:
                    redis:
                        specification:
                            source:
                                image: sroze/my-image
    """

  Scenario: It will ignore when unable to identify the DockerCompose version
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        images:
            build:
                services:
                    api:
                        image: docker.io/sroze/api
    """
    And I have a "docker-compose.yml" file in my repository that contains:
    """
    ersion: '2'
    services:
        api:
            build:
                context: .
                dockerfile: docker/style-guide/Dockerfile
    """
    When the configuration of the tide is generated
    Then the generated configuration should contain at least:
    """
    tasks:
        images:
            build:
                services:
                    api:
                        image: docker.io/sroze/api
    """
