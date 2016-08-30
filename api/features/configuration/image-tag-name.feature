Feature:
  In order to ease the building process
  As a developer
  I want my images to be built by default with a tag that come from the code repository branch name

  Scenario: The commit SHA1 is the default image name
    Given there is 1 application images in the repository
    And I have a flow with the following configuration:
    """
    tasks:
        images:
            build: ~
    """
    When a tide is started for the branch "my-feature" and commit "3b0110193e36b317207909163d0a582f6f568cf8"
    Then the image tag "3b0110193e36b317207909163d0a582f6f568cf8" should be built

  Scenario: The name of the tag should come from the reference of the pull-request
    Given I have a flow with the following configuration:
    """
    tasks:
        images:
            build:
                services:
                    image0:
                        naming_strategy: branch
    """
    And there is 1 application images in the repository
    When the pull request #1 is opened with head "feature/dc-labels" from another repository labelled "sroze"
    And the tide starts
    Then the image tag "sroze-feature-dc-labels" should be built

  Scenario: Can use the branch name as naming strategy
    Given I have a flow with the following configuration:
    """
    tasks:
        images:
            build:
                services:
                    image0:
                        naming_strategy: branch
    """
    And there is 1 application images in the repository
    When the pull request #1 is opened with head "feature/dc-labels" from another repository labelled "sroze"
    And the tide starts
    Then the image tag "sroze-feature-dc-labels" should be built
