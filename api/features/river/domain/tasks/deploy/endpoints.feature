Feature:
  In order to be able to access my deployed application
  As a developer
  I want to be able to configure endpoints

  Scenario: Add endpoints
    When a tide is started with the following configuration:
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
    When a tide is started with the following configuration:
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
    When a tide is started with the following configuration:
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
    When a tide is started with the following configuration:
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
    When a tide is started with the following configuration:
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
                                        - name: basic_authentication
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

  Scenario: HttpLabs proxy with specific dns name
    When a tide is started for the branch "my-very-long-shiny-new-feature-branch-name" with the following configuration:
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
                                    host:
                                        expression: 'hash_long_domain_prefix(code_reference.branch, 27) ~ "-certeo.inviqa-001.httplabs.net"'


                        specification:
                            source:
                                image: my/app
                            ports:
                                - 80
    """
    Then the component "app" should be deployed
    And the component "app" should be deployed with an endpoint named "http"
    And the endpoint "http" of the component "app" should be deployed with an HttpLabs configuration for the project "7890" and API key "123456"
    And the endpoint "http" of the component "app" should be deployed with an HttpLabs host "my-very-long-shi-02b27a5635-certeo.inviqa-001.httplabs.net"

  Scenario: HttpLabs proxy with specific dns name using record suffix
    When a tide is started for the branch "my-very-long-shiny-new-feature-branch-name" with the following configuration:
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
                                    record_suffix: "-certeo.inviqa-001.httplabs.net"

                        specification:
                            source:
                                image: my/app
                            ports:
                                - 80
    """
    Then the component "app" should be deployed
    And the component "app" should be deployed with an endpoint named "http"
    And the endpoint "http" of the component "app" should be deployed with an HttpLabs configuration for the project "7890" and API key "123456"
    And the endpoint "http" of the component "app" should be deployed with an HttpLabs host "my-very-long-shiny-new-02b27a5635-certeo.inviqa-001.httplabs.net"

  Scenario: Add endpoints annotations
    When a tide is started with the following configuration:
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
    When a tide is started with the following configuration:
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
    When a tide is started with the following configuration:
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
                                        expression: 'code_reference.branch ~ "-certeo.inviqa-001.example.com"'

                        specification:
                            source:
                                image: my/app
                            ports:
                                - 80
    """
    Then the component "app" should be deployed
    And the component "app" should be deployed with an endpoint named "http"
    And the endpoint "http" of the component "app" should be deployed with an ingress with the host "master-certeo.inviqa-001.example.com"

  Scenario: If the branch name contains non valid characters, the ingress host name can be slugified
    When a tide is started for the branch "feature/123-foo-bar" with the following configuration:
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
                                        expression: 'slugify(code_reference.branch) ~ "-certeo.inviqa-001.example.com"'

                        specification:
                            source:
                                image: my/app
                            ports:
                                - 80
    """
    Then the component "app" should be deployed
    And the component "app" should be deployed with an endpoint named "http"
    And the endpoint "http" of the component "app" should be deployed with an ingress with the host "feature-123-foo-bar-certeo.inviqa-001.example.com"

  Scenario: If the branch name is too long, the host name can be hashed with a custom function
    When a tide is started for the branch "my-very-long-shiny-new-feature-branch-name" with the following configuration:
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
                                        expression: 'hash_long_domain_prefix(code_reference.branch, 27) ~ "-certeo.inviqa-001.example.com"'

                        specification:
                            source:
                                image: my/app
                            ports:
                                - 80
    """
    Then the component "app" should be deployed
    And the component "app" should be deployed with an endpoint named "http"
    And the endpoint "http" of the component "app" should be deployed with an ingress with the host "my-very-long-shi-02b27a5635-certeo.inviqa-001.example.com"

  Scenario: The host_suffix key can be used to simplify slugifying and shortening hostnames
    When a tide is started for the branch "feature/my-very-long-shiny-new-branch-name" with the following configuration:
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
                                    host_suffix: "-certeo.inviqa-001.example.com"

                        specification:
                            source:
                                image: my/app
                            ports:
                                - 80
    """
    Then the component "app" should be deployed
    And the component "app" should be deployed with an endpoint named "http"
    And the endpoint "http" of the component "app" should be deployed with an ingress with the host "feature-my-very-c5743d6c37-certeo.inviqa-001.example.com"

  Scenario: The host_suffix cannot be too long
    When a tide is started for the branch "feature/new-branch-name" with the following configuration:
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
                                    host_suffix: "my-very-long-host-suffix-certeo.inviqa-001.example.com"

                        specification:
                            source:
                                image: my/app
                            ports:
                                - 80
    """
    Then the tide should be failed
    And a log containing 'The ingress host_suffix cannot be more than 53 characters long' should be created

  Scenario: Add the CloudFlare backend manually
    When a tide is started with the following configuration:
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
    When a tide is started with the following configuration:
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
                                        expression: 'code_reference.branch ~ "-certeo.inviqa-001.example.com"'

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

  Scenario: A wrong tide expression do not fail dramatically
    When a tide is started with the following configuration:
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
                                        expression: 'certeo.inviqa-001.example.com'

                        specification:
                            source:
                                image: my/app
                            ports:
                                - 80
    """
    Then the tide should be failed
    And a log containing 'The expression provided ("certeo.inviqa-001.example.com") is not valid' should be created

  Scenario: The hostname is generated for cloudflare
    When a tide is started with the following configuration:
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
    And the endpoint "http" of the component "app" should be deployed with a CloudFlare DNS zone configuration with hostname "master.example.com"

  Scenario: Create cloudflare dns configuration with host expression
    When a tide is started with the following configuration:
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
                                    host:
                                        expression: 'code_reference.branch ~ ".certeo.inviqa-001.example.com"'
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
    And the endpoint "http" of the component "app" should be deployed with a CloudFlare DNS zone configuration with hostname "master.certeo.inviqa-001.example.com"

  Scenario: Create cloudflare dns configuration with slugified host expression
    When a tide is started for the branch "feature/123-foo-bar" with the following configuration:
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
                                    host:
                                        expression: 'slugify(code_reference.branch) ~ "-certeo.inviqa-001.example.com"'
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
    And the endpoint "http" of the component "app" should be deployed with a CloudFlare DNS zone configuration with hostname "feature-123-foo-bar-certeo.inviqa-001.example.com"

  Scenario: Create cloudflare dns configuration with hashed host expression
    When a tide is started for the branch "my-very-long-shiny-new-feature-branch-name" with the following configuration:
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
                                    host:
                                        expression: 'hash_long_domain_prefix(code_reference.branch, 27) ~ "-certeo.inviqa-001.example.com"'
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
    And the endpoint "http" of the component "app" should be deployed with a CloudFlare DNS zone configuration with hostname "my-very-long-shi-02b27a5635-certeo.inviqa-001.example.com"

  Scenario: The host_suffix key can be used to simplify slugifying and shortening CloudFlare hostnames
    When a tide is started for the branch "feature/my-very-long-shiny-new-branch-name" with the following configuration:
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
                                    record_suffix: "-certeo.inviqa-001.example.com"
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
    And the endpoint "http" of the component "app" should be deployed with a CloudFlare DNS zone configuration with hostname "feature-my-very-c5743d6c37-certeo.inviqa-001.example.com"

  @smoke
  Scenario: A self-signed SSL certificate can be generated automatically for the hostname
    When a tide is started for the branch "my-feature" with the following configuration:
    """
    tasks:
        first:
            deploy:
                cluster: foo
                services:
                    app:
                        endpoints:
                            -
                                name: app
                                ingress:
                                    class: nginx
                                    host:
                                        expression: 'code_reference.branch ~ "-12357-flex.example.com"'

                                cloud_flare_zone:
                                    zone_identifier: 123456
                                    authentication:
                                        email: sam@example.com
                                        api_key: qwerty1234567890

                                ssl_certificates:
                                    - name: automatic
                                      cert: automatic
                                      key: automatic

                        specification:
                            source:
                                image: my/app
                            ports:
                                - 80
    """
    Then the component "app" should be deployed
    And the component "app" should be deployed with an endpoint named "app"
    And the endpoint "app" of the component "app" should be deployed with a CloudFlare DNS zone configuration
    And the endpoint "app" of the component "app" should be deployed with 1 SSL certificate
    And the endpoint "app" of the component "app" should be deployed with a SSL certificate for the hostname "my-feature-12357-flex.example.com"

  Scenario: A self-signed SSL certificate can be generated automatically for the hostname, regardless the name
    When a tide is started for the branch "my-feature" with the following configuration:
    """
    tasks:
        first:
            deploy:
                cluster: foo
                services:
                    app:
                        endpoints:
                            -
                                name: app
                                ingress:
                                    class: nginx
                                    host:
                                        expression: 'code_reference.branch ~ "-12357-flex.example.com"'
                                cloud_flare_zone:
                                    zone_identifier: 123456
                                    authentication:
                                        email: sam@example.com
                                        api_key: qwerty1234567890
                                ssl_certificates:
                                    - name: app
                                      cert: automatic
                                      key: automatic
                        specification:
                            source:
                                image: my/app
                            ports:
                                - 80
    """
    Then the component "app" should be deployed
    And the component "app" should be deployed with an endpoint named "app"
    And the endpoint "app" of the component "app" should be deployed with a CloudFlare DNS zone configuration
    And the endpoint "app" of the component "app" should be deployed with 1 SSL certificate
    And the endpoint "app" of the component "app" should be deployed with a SSL certificate for the hostname "my-feature-12357-flex.example.com"

  Scenario: I can use directly the host, without an expression
    When a tide is started for the branch "feature/my-very-long-shiny-new-branch-name" with the following configuration:
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
                                    host: continuouspipe.github.io

                        specification:
                            source:
                                image: my/app
                            ports:
                                - 80
    """
    Then the component "app" should be deployed
    And the component "app" should be deployed with an endpoint named "http"
    And the endpoint "http" of the component "app" should be deployed with an ingress with the host "continuouspipe.github.io"

  Scenario: Non-matching condition means the endpoint is not used
    When a tide is started for the branch "master" with the following configuration:
    """
    tasks:
        first:
            deploy:
                cluster: foo
                services:
                    app:
                        endpoints:
                            - name: production
                              ingress:
                                class: nginx
                                host: continuouspipe.github.io
                              condition: code_reference.branch == 'production'
                            - name: http
                              ingress:
                                class: nginx
                                host_suffix: -12357-flex.example.com

                        specification:
                            source:
                                image: my/app
                            ports:
                                - 80
    """
    Then the component "app" should be deployed
    And the component "app" should be deployed with an endpoint named "http"
    And the component "app" should not be deployed with an endpoint named "production"
    And the endpoint "http" of the component "app" should be deployed with an ingress with the host "master-12357-flex.example.com"

  Scenario: Matching condition means the endpoint is used
    When a tide is started for the branch "production" with the following configuration:
    """
    tasks:
        first:
            deploy:
                cluster: foo
                services:
                    app:
                        endpoints:
                            - name: production
                              ingress:
                                class: nginx
                                host: continuouspipe.github.io
                              condition: code_reference.branch == 'production'
                            - name: http
                              ingress:
                                class: nginx
                                host_suffix: -12357-flex.example.com

                        specification:
                            source:
                                image: my/app
                            ports:
                                - 80
    """
    Then the component "app" should be deployed
    And the component "app" should be deployed with an endpoint named "http"
    And the endpoint "http" of the component "app" should be deployed with an ingress with the host "production-12357-flex.example.com"
    And the endpoint "production" of the component "app" should be deployed with an ingress with the host "continuouspipe.github.io"
