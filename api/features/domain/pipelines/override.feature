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
    And I have a "docker-compose.yml" file in my repository that contains:
    """
    app:
        image: docker.io/continuouspipe/landing-page
    database:
        image: mysql
    mailcatcher:
        image: mailcatcher
    """
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    variables:
        - name: RICHARD
          value: MILLER
        - name: BAR_VALUE
          value: bar

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
                            environment_variables:
                                - name: FOO
                                  value: foo
                                - name: BAR
                                  value: ${BAR_VALUE}
                                - name: RICHARD
                                  value: ${RICHARD}
                    database: ~

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
                                      value: ${BAR_VALUE}
        - name: To develop
          condition: code_reference.branch == 'develop'
          tasks:
              - imports: deployment
                deploy:
                    environment:
                        name: '"ui-ci"'
        - name: Only the branches
          condition: code_reference.branch not in ['develop', 'master']
          variables:
              - name: RICHARD
                value: JONES
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

  Scenario: It doesn't deploy services that aren't defined in the pipeline when task services not overriden
    When I send a tide creation request for branch "develop" and commit "1234"
    And the tide for the branch "develop" and commit "1234" is tentatively started
    Then a tide should be created
    And the component "database" should be deployed
    And the component "mailcatcher" should not be deployed

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

  Scenario: It is using variables normally
    When I send a tide creation request for branch "master" and commit "5678"
    And the tide for the branch "master" and commit "5678" is tentatively started
    Then a tide should be created
    And the component "app" should be deployed with the following environment variables:
      | name    | value  |
      | RICHARD | MILLER |

  Scenario: It can override some variables
    When I send a tide creation request for branch "a-feature" and commit "5678"
    And the tide for the branch "a-feature" and commit "5678" is tentatively started
    Then a tide should be created
    And the component "app" should be deployed with the following environment variables:
      | name    | value  |
      | RICHARD | JONES  |
