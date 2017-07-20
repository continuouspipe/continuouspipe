Feature:
  In order to deploy by Symfony Flex application seamlessly
  As a user
  I want CP to generate my configuration for me

  Background:
    Given I have a flow with UUID "00000000-0000-0000-0000-000000000000"
    And the flow "00000000-0000-0000-0000-000000000000" has flex activated

  Scenario: It generates a basic configuration for Symfony
    Given the code repository contains the fixtures folder "flex-skeleton"
    When the configuration of the tide is generated
    Then the generated configuration should contain at least:
    """
    tasks:
        images:
            build:
                services:
                    app:
                        image: quay.io/continuouspipe-flex/flow-00000000-0000-0000-0000-000000000000

        app_deployment:
            deploy:
                services:
                    app:
                        endpoints:
                            - name: app
                              cloud_flare_zone:
                                  zone_identifier: ${CLOUD_FLARE_ZONE}
                                  authentication:
                                      email: ${CLOUD_FLARE_EMAIL}
                                      api_key: ${CLOUD_FLARE_API_KEY}
                              ingress:
                                  class: nginx
                                  host_suffix: '00000000-0000-0000-0000-000000000000-flex.continuouspipe.net'
    """
