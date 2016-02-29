Feature:
  In order to ease the building process
  As a developer
  I want my images to be built by default with a tag that come from the code repository branch name

  Scenario: If the branch name is a valid image tag, just keep it
    Given there is 1 application images in the repository
    When a tide is started for the branch "my-feature" with a build task
    Then the image tag "my-feature" should be built

  Scenario: If the branch name contains invalid characters, slugify the name
    Given there is 1 application images in the repository
    When a tide is started for the branch "feature/my-foo" with a build task
    Then the image tag "feature-my-foo" should be built

  Scenario: The name of the tag should come from the reference of the pull-request
    Given I have a flow with the following configuration:
    """
    tasks:
        - build: ~
    """
    And there is 1 application images in the repository
    When the pull request #1 is opened with head "feature/dc-labels" from another repository labelled "sroze"
    And the tide starts
    Then the image tag "sroze-feature-dc-labels" should be built
