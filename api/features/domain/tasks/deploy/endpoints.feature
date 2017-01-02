Feature:
  In order to be able to access my deployed application
  As a developer
  I want to be able to configure endpoints

  Scenario: Add endpoints
    When I tide is started with the following configuration:
    """
    tasks:
        first:
            deploy:
                cluster: foo
                services:
                    app:
                        endpoints:
                            -
                                name: https
                                type: NodePort
                                ssl_certificates:
                                    -
                                        name: continuouspipeio
                                        cert: VALUE
                                        key: VALUE
                        specification:
                            source:
                                image: my/app
                            accessibility:
                                from_external: true
                            ports:
                                - 80
    """
    Then the component "app" should be deployed
    And the component "app" should be deployed with an endpoint named "https"
    And the endpoint "https" of the component "app" should be deployed with 1 SSL certificate

  Scenario: Add the CloudFlare configuration
    When I tide is started with the following configuration:
    """
    tasks:
        first:
            deploy:
                cluster: foo
                services:
                    app:
                        endpoints:
                            -
                                name: http
                                cloud_flare_zone:
                                    zone_identifier: 123456
                                    record_suffix: .example.com
                                    authentication:
                                        email: sam@example.com
                                        api_key: qwerty1234567890

                        specification:
                            source:
                                image: my/app
                            ports:
                                - 80
    """
    Then the component "app" should be deployed
    And the component "app" should be deployed with an endpoint named "http"
    And the endpoint "http" of the component "app" should be deployed with a CloudFlare DNS zone configuration

  Scenario: The authentication is a required piece of information for CloudFlare
    When I tide is started with the following configuration:
    """
    tasks:
        first:
            deploy:
                cluster: foo
                services:
                    app:
                        endpoints:
                            -
                                name: http
                                cloud_flare_zone:
                                    zone_identifier: 123456wertyu
                                    record_suffix: .example.com

                        specification:
                            source:
                                image: my/app
                            ports:
                                - 80
    """
    Then the tide should be failed
