Feature:
  In order to have a coherent tide
  As a developer
  I expect the images built in this tide to be deployed

  Scenario:
    Given there is 1 application images in the repository
    When a tide is started for the branch "my-feature" with a build and deploy task
    Then the image tag "my-feature" should be built

  Scenario:
    Given there is 1 application images in the repository
    When a tide is started for the branch "my-feature" with a build and deploy task
    And all the image builds are successful
    Then the deployed image tag should be "my-feature"

  Scenario:
    Given there is 1 application images in the repository
    When a tide is started with a deploy task
    Then the deployed environment name should be prefixed by the flow identifier
