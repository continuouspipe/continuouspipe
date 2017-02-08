Feature:
  In order to run tides depending on a PR status
  As a developer
  I want to be able to filter tides based on the pull-request status

  Scenario: With PR label only, the tide shouldn't be created with a simple push
    Given I have a flow with the following configuration:
    """
    tasks: [{build: {services: []}}]
    filter: '"Ready for QA" in pull_request.labels'
    """
    When the commit "3b0110193e36b317207909163d0a582f6f568cf8" is pushed to the branch "master"
    Then the tide should not be created

  Scenario: With PR label only, the tide shouldn't be created if the pull-request do not contain the label
    Given I have a flow with the following configuration:
    """
    tasks: [{build: {services: []}}]
    filter: '"Ready for QA" in pull_request.labels'
    """
    When the pull request #1 is opened
    Then the tide should not be created

  Scenario: With PR label only, the tide should not be created if the branch is synchronized
    Given I have a flow with the following configuration:
    """
    tasks: [{build: {services: []}}]
    filter: '"Ready for QA" in pull_request.labels'
    """
    When the pull request #1 is synchronized
    Then the tide should not be created

  Scenario: With PR label only, the tide shouldn't be created if the pull-request do not contain the label
    Given I have a flow with the following configuration:
    """
    tasks: [{build: {services: []}}]
    filter: '"Ready for QA" in pull_request.labels'
    """
    And the pull request #1 have the label "Ready for QA"
    When the pull request #1 is synchronized
    Then the tide should be created

  Scenario: Filter on the branch name
    Given I have a flow with the following configuration:
    """
    tasks: [{build: {services: []}}]
    filter: 'code_reference.branch == "master" || "Ready for QA" in pull_request.labels'
    """
    And the pull request #1 have the label "Ready for QA"
    When the pull request #1 is synchronized
    Then the tide should be created

  Scenario: When a PR is labeled, it should start the tide
    Given I have a flow with the following configuration:
    """
    tasks: [{build: {services: []}}]
    filter: '"Ready for QA" in pull_request.labels'
    """
    And the pull request #1 have the label "Ready for QA"
    When the pull request #1 is labeled
    Then the tide should be created

  Scenario: Uses a pull-request filter on pipelines
    Given I have a flow with the following configuration:
    """
    tasks:
        images: {build: {services: []}}

    pipelines:
      - name: My pipeline
        tasks: [ images ]
        condition: '"Ready for QA" in pull_request.labels'
    """
    And the GitHub pull-request #1 contains the tide-related commit
    And the pull request #1 have the label "Ready for QA"
    When the pull request #1 is labeled
    Then the tide should be created
    And the tide should not be failed

  Scenario: Uses a pull-request titles in filters
    Given I have a flow with the following configuration:
    """
    tasks:
        images: {build: {services: []}}

    pipelines:
      - name: My pipeline
        tasks: [ images ]
        condition: 'pull_request.title matches "#^something#"'
    """
    And the GitHub pull-request #1 titled "something-is-good" contains the tide-related commit
    When the pull request #1 is synchronized
    Then the tide should be created
    And the tide should not be failed
