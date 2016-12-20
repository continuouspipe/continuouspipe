Feature:
  In order to simply my pipeline configurations
  As a developer
  I want to be able to simply override some tasks' configuration in pipelines

  Background:
    Given I am authenticated as "samuel"
    And the team "samuel" exists
    And the user "samuel" is "USER" of the team "samuel"
    And the head commit of branch "master" is "1234"
    And I have a flow
    And there is 1 application images in the repository
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    defaults:
        environment:
            name: '"ui-" ~ code_reference.branch'

        cluster: foo

    tasks:
        deployment:
            deploy:
                services:
                    app:
                        specification:
                            source:
                                image: docker.io/continuouspipe/landing-page
                            environment_variables:
                                - name: FOO
                                  value: foo
                                - name: BAR
                                  value: bar
                    database:
                        specification:
                            source:
                                image: mysql

    pipelines:
        - name: To master
          condition: code_reference.branch == 'master'
          tasks:
              - imports: deployment
                deploy:
                    services:
                        app:
                            specification:
                                environment_variables:
                                    - name: FOO
                                      value: bar


        - name: Only the branches
          condition: code_reference.branch != 'master'
          tasks:
              - imports: deployment
                deploy:
                    services:
                        database:
                            specification:
                                environment_variables:
                                    - name: BAZ
                                      value: baz
                        app:
                            specification:
                                accessibility:
                                    from_external: true
    """

  Scenario: It overrides the environment variables
    When I send a tide creation request for branch "master" and commit "1234"
    And the tide for the branch "master" and commit "1234" is tentatively started
    Then a tide should be created
    And the component "app" should be deployed with the following environment variables:
     | name | value |
     | FOO  | bar   |
     | BAR  | bar   |

  Scenario: It can add some environment variables
    When I send a tide creation request for branch "feature/my-branch" and commit "5678"
    And the tide for the branch "feature/my-branch" and commit "5678" is tentatively started
    Then a tide should be created
    And the component "app" should be deployed with the following environment variables:
      | name | value |
      | FOO  | foo   |
      | BAR  | bar   |
    And the component "database" should be deployed with the following environment variables:
      | BAZ  | baz   |

  Scenario: I can override anything such as the accessibility
    When I send a tide creation request for branch "feature/my-branch" and commit "9012"
    And the tide for the branch "feature/my-branch" and commit "9012" is tentatively started
    Then a tide should be created
    And the component "app" should be deployed as accessible from outside
