Feature:
  In order to have a coherent tide
  As a developer
  I expect the images built in this tide to be deployed

  Scenario: The deployed image will have the tag name of the branch
    Given there is 1 application images in the repository
    When a tide is started for the branch "my-feature" with a build and deploy task
    And all the image builds are successful
    Then the deployed image named "image0" should should be tagged "my-feature"

  Scenario: Deployed environment prefix
    Given there is 1 application images in the repository
    When a tide is started with a deploy task
    Then the deployed environment name should be prefixed by the flow identifier

  Scenario: Using image from private registry
    Given I have a "docker-compose.yml" file in my repository that contains:
    """
    app:
        build: .
        labels:
            com.continuouspipe.image-name: docker.io/foo/bar
    """
    When a tide is started for the branch "qwerty" with a build and deploy task
    And all the image builds are successful
    Then the deployed image named "docker.io/foo/bar" should should be tagged "qwerty"
    And the deployed image name should be "docker.io/foo/bar"
