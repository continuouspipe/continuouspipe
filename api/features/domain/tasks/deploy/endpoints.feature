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

  Scenario: HttpLabs proxy without middleware
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
                                httplabs:
                                    api_key: 123456
                                    project_identifier: 7890

                        specification:
                            source:
                                image: my/app
                            ports:
                                - 80
    """
    Then the component "app" should be deployed
    And the component "app" should be deployed with an endpoint named "http"
    And the endpoint "http" of the component "app" should be deployed with an HttpLabs configuration for the project "7890" and API key "123456"

  Scenario: HttpLabs proxy with middlewares
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
                                httplabs:
                                    api_key: 123456
                                    project_identifier: 7890
                                    middlewares:
                                        - template: https://api.httplabs.io/projects/13d1ab08-0eca-4289-aa8b-132bc569fe3f/templates/basic_authentication
                                          config:
                                              realm: This is secure!
                                              username: username
                                              password: password

                        specification:
                            source:
                                image: my/app
                            ports:
                                - 80
    """
    Then the component "app" should be deployed
    And the component "app" should be deployed with an endpoint named "http"
    And the endpoint "http" of the component "app" should be deployed with an HttpLabs configuration that have 1 middleware

  Scenario: Add endpoints annotations
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
                                annotations:
                                    service.beta.kubernetes.io/external-traffic: OnlyLocal

                        specification:
                            source:
                                image: my/app
                            ports:
                                - 80
    """
    Then the component "app" should be deployed
    And the component "app" should be deployed with an endpoint named "http"
    And the endpoint "http" of the component "app" should be deployed with the following annotations:
      | name                                        | value     |
      | service.beta.kubernetes.io/external-traffic | OnlyLocal |

  Scenario: Configure CloudFlare proxied & ttl options
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
                                    proxied: true
                                    ttl: 1800
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
    And the endpoint "http" of the component "app" should be deployed with a proxied CloudFlare DNS zone configuration

  Scenario: Create ingresses endpoints with hosts
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
                                ingress:
                                    class: nginx
                                    host:
                                        expression: 'code_reference.branch ~ "-certeo.inviqa-001.continuouspipe.net"'

                        specification:
                            source:
                                image: my/app
                            ports:
                                - 80
    """
    Then the component "app" should be deployed
    And the component "app" should be deployed with an endpoint named "http"
    And the endpoint "http" of the component "app" should be deployed with an ingress with the host "master-certeo.inviqa-001.continuouspipe.net"

  Scenario: Add the CloudFlare backend manually
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
                                    backend_address: 1.2.3.4
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
    And the endpoint "http" of the component "app" should be deployed with a CloudFlare DNS zone configuration with the backend "1.2.3.4"

  Scenario: CloudFlare do not require record prefix with the ingresses
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
                                ingress:
                                    class: nginx
                                    host:
                                        expression: 'code_reference.branch ~ "-certeo.inviqa-001.continuouspipe.net"'

                                cloud_flare_zone:
                                    zone_identifier: 123456
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
