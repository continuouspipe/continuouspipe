Feature:
  In order to have a coherent tide
  As a developer
  I expect the images built in this tide to be deployed

  Scenario:
    Given I have a flow with the build and deploy tasks
    And there is 1 application images in the repository
    When a tide is started for the branch "my-feature"
    Then the image tag "my-feature" should be built

  Scenario:
    Given I have a flow with the build and deploy tasks
    And there is 1 application images in the repository
    When a tide is started for the branch "my-feature"
    And all the image builds are successful
    Then the deployed image tag should be "my-feature"

  Scenario:
    Given I have a flow with a deploy task
    And there is 1 application images in the repository
    When a tide is started based on that workflow
    Then the deployed environment name should be prefixed by the flow identifier
