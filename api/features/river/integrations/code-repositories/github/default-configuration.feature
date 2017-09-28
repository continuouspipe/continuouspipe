Feature:
  In order to ease the building process
  As a developer
  I want my images to be built by default with a tag that come from the code repository branch name

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
