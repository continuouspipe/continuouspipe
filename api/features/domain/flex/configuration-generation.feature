Feature:
  In order to deploy by Symfony Flex application seamlessly
  As a user
  I want CP to generate my configuration for me

  Scenario: It generates a basic configuration for Symfony
    Given I have a flow with UUID "00000000-0000-0000-0000-000000000000"
    And the flow "00000000-0000-0000-0000-000000000000" has been flex activated with the same identifier "abc123"
    And the code repository contains the fixtures folder "flex-skeleton"
    When the configuration of the tide is generated
    Then the generated configuration should contain at least:
    """
    variables:
        - name: CLOUD_FLARE_ZONE
          encrypted_value: em9uZQ==
        - name: CLOUD_FLARE_EMAIL
          encrypted_value: ZW1haWw=
        - name: CLOUD_FLARE_API_KEY
          encrypted_value: YXBpX2tleQ==

    tasks:
        0_images:
            build:
                services:
                    app:
                        image: quay.io/continuouspipe-flex/flow-00000000-0000-0000-0000-000000000000

        2_app_deployment:
            deploy:
                services:
                    app:
                        endpoints:
                            - name: app
                              cloud_flare_zone:
                                  zone_identifier: zone
                                  authentication:
                                      email: email
                                      api_key: api_key
                              ingress:
                                  class: nginx
                                  host_suffix: '-abc123-flex.continuouspipe.net'
    """

  Scenario: The environment name is based on the flow UUID
    Given I have a flow with UUID "00000000-0000-0000-0000-000000000000"
    And the flow "00000000-0000-0000-0000-000000000000" has been flex activated with the same identifier "abc123"
    And the code repository contains the fixtures folder "flex-skeleton"
    When the configuration of the tide is generated
    Then the generated configuration should contain at least:
    """
    defaults:
        environment:
            name: "'00000000-0000-0000-0000-000000000000-' ~ code_reference.branch"
    """

  Scenario: It adds a database when Symfony has Doctrine enabled
    Given I have a flow with UUID "00000000-0000-0000-0000-000000000000"
    And the flow "00000000-0000-0000-0000-000000000000" has been flex activated with the same identifier "abc123"
    And the code repository contains the fixtures folder "flex-skeleton"
    And the ".env.dist" file in the code repository contains:
    """
    ###> symfony/framework-bundle ###
    APP_ENV=dev
    APP_DEBUG=1
    APP_SECRET=547417d8a21a468aa18ba068702c0e9a
    ###< symfony/framework-bundle ###

    ###> doctrine/doctrine-bundle ###
    # Format described at http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#connecting-using-a-url
    # For a sqlite database, use: "sqlite:///%kernel.project_dir%/var/data.db"
    # Set "serverVersion" to your server version to avoid edge-case exceptions and extra database calls
    DATABASE_URL=mysql://foo:bar@postgres/baz
    ###< doctrine/doctrine-bundle ###
    """
    When the configuration of the tide is generated
    Then the generated configuration should contain at least:
    """
    tasks:
        1_database_deployment:
            deploy:
                services:
                    database:
                        specification:
                            ports:
                                - { identifier: database5432, port: 5432, protocol: TCP }

        2_app_deployment:
            deploy:
                services:
                    app:
                        specification:
                            environment_variables:
                                - { name: APP_ENV, value: dev }
                                - { name: APP_DEBUG, value: '1' }
                                - { name: APP_SECRET, value: 547417d8a21a468aa18ba068702c0e9a }
                                - { name: DATABASE_URL, value: postgres://app:app@database/app }
    """

  Scenario: It will build the image with the environment as build arguments
    Given I have a flow with UUID "00000000-0000-0000-0000-000000000000"
    And the flow "00000000-0000-0000-0000-000000000000" has been flex activated with the same identifier "abc123"
    And the code repository contains the fixtures folder "flex-skeleton"
    When the configuration of the tide is generated
    Then the generated configuration should contain at least:
    """
    tasks:
        0_images:
            build:
                services:
                    app:
                        environment:
                            - name: APP_ENV
                            - name: APP_DEBUG
                            - name: APP_SECRET
    """

  Scenario: Override the env.dist values by using variables
    Given I have a flow with UUID "00000000-0000-0000-0000-000000000000" and the following configuration:
    """
    variables:
    - name: FOO
      value: my-foo
    """
    And the flow "00000000-0000-0000-0000-000000000000" has been flex activated with the same identifier "abc123"
    And the code repository contains the fixtures folder "flex-skeleton"
    And the ".env.dist" file in the code repository contains:
    """
    ###> symfony/framework-bundle ###
    APP_ENV=dev
    APP_DEBUG=1
    APP_SECRET=547417d8a21a468aa18ba068702c0e9a
    ###< symfony/framework-bundle ###

    FOO=foo
    BAR=bar
    """
    When the configuration of the tide is generated
    Then the generated configuration should contain at least:
    """
    tasks:
        0_images:
            build:
                services:
                    app:
                        environment:
                            - name: APP_ENV
                            - name: APP_DEBUG
                            - name: APP_SECRET
                            - name: FOO
                              value: my-foo
                            - name: BAR
                              value: bar
    """
