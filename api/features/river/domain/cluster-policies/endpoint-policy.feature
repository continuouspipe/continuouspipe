Feature:
  In order to have endpoints created in a unified manner across the cluster
  As a DevOps engineer
  I want my endpoint policy to be the one used specifically when a user requires a public endpoint for a container

  Background:
    Given the team "my-team" exists
    And there is a user "samuel"
    And the user "samuel" is "ADMIN" of the team "my-team"
    And I have a flow with UUID "00000000-0000-0000-0000-000000000000" in the team "my-team"

  Scenario: Uses policy defaults
    Given the cluster "flex" of the team "my-team" have the following policies:
    """
    [
      {
        "name": "endpoint",
        "configuration": {
          "type": "ingress",
          "ingress-class": "nginx",
          "ingress-host-suffix": "-1234.example.com"
        }
      }
    ]
    """
    Given the team "my-team" have the credentials of a cluster "foo"
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        my_deployment:
            deploy:
                cluster: flex
                services:
                    app:
                        specification:
                            source:
                                image: foo/bar

                        endpoints:
                            - name: app
    """
    When a tide is started for the branch "master"
    Then the endpoint "app" of the component "app" should be deployed with an ingress with the host "master-app-1234.example.com"

  Scenario: Default to the policy instead of deprecated accessibility
    Given the cluster "flex" of the team "my-team" have the following policies:
    """
    [
      {
        "name": "endpoint",
        "configuration": {
          "type": "ingress",
          "ingress-class": "nginx",
          "ingress-host-suffix": "-1234.example.com"
        }
      }
    ]
    """
    Given the team "my-team" have the credentials of a cluster "foo"
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        my_deployment:
            deploy:
                cluster: flex
                services:
                    app:
                        specification:
                            source:
                                image: foo/bar
                            accessibility:
                                from_external: true
    """
    When a tide is started for the branch "master"
    Then the endpoint "app" of the component "app" should be deployed with an ingress with the host "master-app-1234.example.com"

  Scenario: The deployment fails if the type is different
    Given the cluster "flex" of the team "my-team" have the following policies:
    """
    [
      {
        "name": "endpoint",
        "configuration": {
          "type": "ingress",
          "ingress-class": "nginx",
          "ingress-host-suffix": "-1234.example.com"
        }
      }
    ]
    """
    Given the team "my-team" have the credentials of a cluster "foo"
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        my_deployment:
            deploy:
                cluster: flex
                services:
                    app:
                        specification:
                            source:
                                image: foo/bar

                        endpoints:
                            - name: app
                              type: NodePort
    """
    When a tide is started for the branch "master"
    Then the tide should be failed
    And a log containing 'Endpoint "app" has a type "NodePort" while type "ingress" is enforced by the cluster policy' should be created

  Scenario: It creates NodePort endpoints by default
    Given the cluster "flex" of the team "my-team" have the following policies:
    """
    [
      {
        "name": "endpoint",
        "configuration": {
          "type": "NodePort"
        }
      }
    ]
    """
    Given the team "my-team" have the credentials of a cluster "foo"
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        my_deployment:
            deploy:
                cluster: flex
                services:
                    app:
                        specification:
                            source:
                                image: foo/bar

                        endpoints:
                            - name: app
    """
    When a tide is started for the branch "master"
    Then the endpoint "app" of the component "app" should be deployed with the type "NodePort"

  Scenario: The deployment fails if the ingress class is different
    Given the cluster "flex" of the team "my-team" have the following policies:
    """
    [
      {
        "name": "endpoint",
        "configuration": {
          "type": "ingress",
          "ingress-class": "nginx",
          "ingress-host-suffix": "-1234.example.com"
        }
      }
    ]
    """
    Given the team "my-team" have the credentials of a cluster "foo"
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        my_deployment:
            deploy:
                cluster: flex
                services:
                    app:
                        specification:
                            source:
                                image: foo/bar

                        endpoints:
                            - name: app
                              ingress:
                                  class: httplabs
                                  host_suffix: "foo.example.com"
    """
    When a tide is started for the branch "master"
    Then the tide should be failed
    And a log containing 'Ingress class of component "app" is "httplabs" while class "nginx" is enforced by the cluster policy' should be created

  Scenario: The deployment fails if the host suffix is different
    Given the cluster "flex" of the team "my-team" have the following policies:
    """
    [
      {
        "name": "endpoint",
        "configuration": {
          "type": "ingress",
          "ingress-class": "nginx",
          "ingress-host-suffix": "-1234.example.com"
        }
      }
    ]
    """
    Given the team "my-team" have the credentials of a cluster "foo"
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        my_deployment:
            deploy:
                cluster: flex
                services:
                    app:
                        specification:
                            source:
                                image: foo/bar

                        endpoints:
                            - name: app
                              ingress:
                                  host_suffix: "-foo.example.com"
    """
    When a tide is started for the branch "master"
    Then the tide should be failed
    And a log containing 'Ingress hostname of component "app" is "master-foo.example.com" while the suffix "-1234.example.com" is enforced by the cluster policy' should be created

  Scenario: It enables CloudFlare by default with the endpoint policy
    Given the cluster "flex" of the team "my-team" have the following policies:
    """
    [
      {
        "name": "endpoint",
        "configuration": {
          "type": "ingress",
          "ingress-class": "nginx",
          "ingress-host-suffix": "-1234.example.com",
          "cloudflare-by-default": "true",
          "cloudflare-proxied-by-default": "true"
        },
        "secrets": {
          "cloudflare-zone-identifier": "123456",
          "cloudflare-email": "samuel@example.com",
          "cloudflare-api-key": "qwertyuiopasdfghjklzxcvbnm"
        }
      }
    ]
    """
    Given the team "my-team" have the credentials of a cluster "foo"
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        my_deployment:
            deploy:
                cluster: flex
                services:
                    app:
                        specification:
                            source:
                                image: foo/bar

                        endpoints:
                            - name: app
    """
    When a tide is started for the branch "master"
    Then the endpoint "app" of the component "app" should be deployed with an ingress with the host "master-app-1234.example.com"
    And the endpoint "app" of the component "app" should be deployed with a CloudFlare DNS zone configuration with hostname "master-app-1234.example.com"
    And the endpoint "app" of the component "app" should be deployed with a proxied CloudFlare DNS zone configuration

  Scenario: It enables SSL certificates by default with the endpoint policy
    Given the cluster "flex" of the team "my-team" have the following policies:
    """
    [
      {
        "name": "endpoint",
        "configuration": {
          "type": "ingress",
          "ingress-class": "nginx",
          "ingress-host-suffix": "-1234.example.com",
          "ssl-certificate-defaults": "true"
        },
        "secrets": {
          "ssl-certificate-key": "automatic",
          "ssl-certificate-cert": "automatic"
        }
      }
    ]
    """
    Given the team "my-team" have the credentials of a cluster "foo"
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        my_deployment:
            deploy:
                cluster: flex
                services:
                    app:
                        specification:
                            source:
                                image: foo/bar

                        endpoints:
                            - name: app
    """
    When a tide is started for the branch "master"
    Then the endpoint "app" of the component "app" should be deployed with an ingress with the host "master-app-1234.example.com"
    And the endpoint "app" of the component "app" should be deployed with a SSL certificate for the hostname "master-app-1234.example.com"

  Scenario: It supports custom definition of the host suffix
    Given the cluster "flex" of the team "my-team" have the following policies:
    """
    [
      {
        "name": "endpoint",
        "configuration": {
          "type": "ingress",
          "ingress-class": "nginx",
          "ingress-host-suffix": "-test.example.com"
        }
      }
    ]
    """
    Given the team "my-team" have the credentials of a cluster "foo"
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        my_deployment:
            deploy:
                cluster: flex
                services:
                    app:
                        specification:
                            source:
                                image: foo/bar

                        endpoints:
                            - { name: app, ingress: { host_suffix: '-abc-test.example.com' }}
    """
    When a tide is started for the branch "master"
    Then the endpoint "app" of the component "app" should be deployed with an ingress with the host "master-abc-test.example.com"

  Scenario: The ingress rules do not have an http rule
    Given the cluster "flex" of the team "my-team" have the following policies:
    """
    [
      {
        "name": "endpoint",
        "configuration": {
          "type": "ingress",
          "ingress-class": "nginx",
          "ingress-host-suffix": "-test.example.com"
        }
      }
    ]
    """
    Given the team "my-team" have the credentials of a cluster "foo"
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        my_deployment:
            deploy:
                cluster: flex
                services:
                    app:
                        specification:
                            source:
                                image: foo/bar
                            accessibility:
                                from_external: true
    """
    When a tide is started for the branch "master"
    Then the endpoint "app" of the component "app" should be deployed with an ingress with the host "master-app-test.example.com"
    And the endpoint "app" of the component "app" should be an ingress without http rule

  Scenario: Uses default host suffix and host rules
    Given the cluster "flex" of the team "my-team" have the following policies:
    """
    [
      {
        "name": "endpoint",
        "configuration": {
          "type": "ingress",
          "ingress-class": "nginx",

          "default-host-suffix": "-test.example.com",
          "host-rules": [
            {"domain": "example.com", "suffix": "-test.example.com"}
          ]
        }
      }
    ]
    """
    Given the team "my-team" have the credentials of a cluster "foo"
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        my_deployment:
            deploy:
                cluster: flex
                services:
                    app:
                        specification:
                            source:
                                image: foo/bar
                            accessibility:
                                from_external: true
    """
    When a tide is started for the branch "master"
    Then the endpoint "app" of the component "app" should be deployed with an ingress with the host "master-app-test.example.com"

  Scenario: It refuses the host based on the rule
    Given the cluster "flex" of the team "my-team" have the following policies:
    """
    [
      {
        "name": "endpoint",
        "configuration": {
          "type": "ingress",
          "ingress-class": "nginx",

          "default-host-suffix": "-test.example.com",
          "host-rules": [
            {"domain": "example.com", "suffix": "-test.example.com"}
          ]
        }
      }
    ]
    """
    Given the team "my-team" have the credentials of a cluster "foo"
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        my_deployment:
            deploy:
                cluster: flex
                services:
                    app:
                        specification:
                            source:
                                image: foo/bar
                            accessibility:
                                from_external: true
                        endpoints:
                            - name: app
                              ingress:
                                  host_suffix: "foo.example.com"
    """
    When a tide is started for the branch "master"
    Then the tide should be failed
    And a log containing 'Ingress hostname of component "app" is "masterfoo.example.com" while the suffix "-test.example.com" is enforced by the cluster policy' should be created

  Scenario: It allows extra domain names
    Given the cluster "flex" of the team "my-team" have the following policies:
    """
    [
      {
        "name": "endpoint",
        "configuration": {
          "type": "ingress",
          "ingress-class": "nginx",

          "default-host-suffix": "-test.example.com",
          "host-rules": [
            {"domain": "example.com", "suffix": "-test.example.com"}
          ]
        }
      }
    ]
    """
    Given the team "my-team" have the credentials of a cluster "foo"
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        my_deployment:
            deploy:
                cluster: flex
                services:
                    app:
                        specification:
                            source:
                                image: foo/bar
                            accessibility:
                                from_external: true
                        endpoints:
                            - name: app
                            - name: www
                              condition: code_reference.branch == 'master'
                              ingress:
                                  host: www.mydomain.com
    """
    When a tide is started for the branch "master"
    Then the endpoint "app" of the component "app" should be deployed with an ingress with the host "master-app-test.example.com"
    And the endpoint "www" of the component "app" should be deployed with an ingress with the host "www.mydomain.com"
