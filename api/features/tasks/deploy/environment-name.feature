Feature:
  In order to ensure the success of the deployment
  As CP system
  I need to chose the name of the deployed environment

  Scenario: The environment name should contains the branch name if possible
    Given I have a flow with UUID "00000000-0000-0000-0000-000000000000"
    When a tide is started for the branch "my-feature" with a deploy task
    Then the name of the deployed environment should be "00000000-0000-0000-0000-000000000000-my-feature"

  Scenario: If the branch name contains non valid characters, the branch name should be slugified
    Given I have a flow with UUID "00000000-0000-0000-0000-000000000000"
    When a tide is started for the branch "feature/123-foo-bar" with a deploy task
    Then the name of the deployed environment should be "00000000-0000-0000-0000-000000000000-feature-123-foo-bar"

  Scenario: If the branch name is too long for the DNS name, it should strip the name
    Given I have a flow with UUID "00000000-0000-0000-0000-000000000000"
    When a tide is started for the branch "feature/123-foo-bar-my-branch-name-has-a-long-name" with a deploy task
    Then the name of the deployed environment should not be "00000000-0000-0000-0000-000000000000-feature-123-foo-bar-my-branch-name-has-a-long-name"
    And the name of the deployed environment should be less or equals than 63 characters long

  Scenario: The final environment name should be lowercase
    Given I have a flow with UUID "00000000-0000-0000-0000-000000000000"
    When a tide is started for the branch "feature/123-FOO-bar" with a deploy task
    Then the name of the deployed environment should be "00000000-0000-0000-0000-000000000000-feature-123-foo-bar"

  Scenario: I can use an expression for the environment name
    Given I have a flow with the following configuration:
    """
    tasks:
        first:
            deploy:
                cluster: foo

                environment:
                    name: '"river-" ~ code_reference.branch'

                services:
                    app:
                        specification:
                            source:
                                image: my/app
                            accessibility:
                                from_external: true
                            ports:
                                - 80
    """
    When a tide is started for the branch "feature/123-FOO-bar"
    Then the name of the deployed environment should be "river-feature-123-foo-bar"
