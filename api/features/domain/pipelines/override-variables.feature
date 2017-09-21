Feature:
  In order to have specific environment's configuration
  As a user
  I want to be able to override variables

  Background:
    Given I am authenticated as "samuel"
    And the team "samuel" exists
    And the user "samuel" is "USER" of the team "samuel"
    And I have a flow
    And I have a "docker-compose.yml" file in my repository that contains:
    """
    app:
        image: docker.io/continuouspipe/landing-page
    """

  Scenario: I deploys the containers with the correct environment variables
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    variables:
        - name: SYMLINK_ENVIRONMENT
          value: dev
          condition: 'code_reference.branch not in ["production", "uat", "integration"]'
        - name: SYMLINK_ENVIRONMENT
          value: something-else
          condition: 'code_reference.branch in ["production", "uat", "integration"]'

    pipelines:
        - name: Production
          condition: 'code_reference.branch == "production"'
          tasks:
            - deployment
          variables:
              - name: SYMLINK_ENVIRONMENT
                value: production

    tasks:
        deployment:
            deploy:
                cluster: foo
                services:
                    app:
                        specification:
                            environment_variables:
                                - name: SYMLINK_ENVIRONMENT
                                  value: ${SYMLINK_ENVIRONMENT}
    """
    When I send a tide creation request for branch "production" and commit "1234"
    And the tide for the branch "production" and commit "1234" is tentatively started
    Then a tide should be created
    And the component "app" should be deployed with the following environment variables:
      | name                | value       |
      | SYMLINK_ENVIRONMENT | production  |

  Scenario: It keeps variable's condition
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    variables:
        - name: SYMLINK_ENVIRONMENT
          value: dev
          condition: 'code_reference.branch not in ["production", "uat", "integration"]'

    pipelines:
        - name: Production
          condition: 'code_reference.branch == "production"'
          tasks:
            - deployment
          variables:
              - name: SYMLINK_ENVIRONMENT
                value: production

    tasks:
        deployment:
            deploy:
                cluster: foo
                services:
                    app:
                        specification:
                            environment_variables:
                                - name: SYMLINK_ENVIRONMENT
                                  value: ${SYMLINK_ENVIRONMENT}
    """
    When I send a tide creation request for branch "production" and commit "1234"
    And the tide for the branch "production" and commit "1234" is tentatively started
    Then a tide should be created
    And the component "app" should be deployed with the following environment variables:
      | name                | value                   |
      | SYMLINK_ENVIRONMENT | ${SYMLINK_ENVIRONMENT}  |
