Feature:
  In order to deploy by Symfony Flex application seamlessly
  As a user
  I want CP to generate my configuration for me

  Background:
    Given I have a flow with UUID "00000000-0000-0000-0000-000000000000"
    And the flow "00000000-0000-0000-0000-000000000000" has been flex activated with the same identifier "abc123"

  Scenario: It generates a basic configuration for Symfony
    Given the code repository contains the fixtures folder "flex-skeleton"
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
                                  zone_identifier: zone
                                  authentication:
                                      email: email
                                      api_key: api_key
                              ingress:
                                  class: nginx
                                  host_suffix: 'abc123-flex.continuouspipe.net'
    """
