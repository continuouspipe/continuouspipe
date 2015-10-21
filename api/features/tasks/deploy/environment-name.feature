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
