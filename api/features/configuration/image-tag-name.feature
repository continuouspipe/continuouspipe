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

