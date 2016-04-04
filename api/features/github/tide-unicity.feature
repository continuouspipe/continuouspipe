Feature:
  In order to prevent running many tides at the same time
  As a user
  I want only one tide per commit

  @smoke
  Scenario: A push is created and a pull-request is opened
    Given I have a flow with the following configuration:
    """
    tasks: [{build: {services: []}}]
    """
    When the pull request #1 is opened with head "feature/dc-labels" and the commit "7852e7ddae799f381ee9ddb73d6d2ce8acc2f7f9"
    And the commit "7852e7ddae799f381ee9ddb73d6d2ce8acc2f7f9" is pushed to the branch "feature/dc-labels"
    Then only 1 tide should be created

  Scenario: 2 tides should be created if the commit is different
    Given I have a flow with the following configuration:
    """
    tasks: [{build: {services: []}}]
    """
    When the pull request #1 is opened with head "feature/dc-labels" and the commit "7852e7ddae799f381ee9ddb73d6d2ce8acc2f7f9"
    And the commit "6bc5922dd0f5150173302b88ef6838b0c8fe6a11" is pushed to the branch "feature/dc-labels"
    Then only 2 tide should be created

  Scenario: A push and a synchronize event
    Given I have a flow with the following configuration:
    """
    tasks: [{build: {services: []}}]
    """
    When the pull request #1 is synchronized with head "feature/dc-labels" and the commit "7852e7ddae799f381ee9ddb73d6d2ce8acc2f7f9"
    And the commit "7852e7ddae799f381ee9ddb73d6d2ce8acc2f7f9" is pushed to the branch "feature/dc-labels"
    Then only 1 tide should be created

  Scenario: Rerun tide if filters changed on the same commit
    Given there is 1 application images in the repository
    And I have a flow with the following configuration:
    """
    tasks:
        images:
            build:
                services: []

            filter:
                expression: '"Ready for QA" in pull_request.labels'
    """
    When the pull request #1 is synchronized with head "feature/dc-labels" and the commit "7852e7ddae799f381ee9ddb73d6d2ce8acc2f7f9"
    And the tide starts
    And the tide is successful
    And the commit "7852e7ddae799f381ee9ddb73d6d2ce8acc2f7f9" is pushed to the branch "feature/dc-labels"
    And the pull request #1 have the label "Ready for QA"
    And the pull request #1 is labeled
    And the second tide starts
    Then only 2 tide should be created
    And the build task of the first tide should be skipped
    And the build task of the second tide should be running
