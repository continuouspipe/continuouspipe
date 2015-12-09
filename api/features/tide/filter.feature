Feature:
  In order to have a better control on tides
  We need to be able to setup filters that will prevent the tide to start

  @smoke
  Scenario: By default, the tide is created
    Given I have a flow with the following configuration:
    """
    tasks: [{build: {services: []}}]
    """
    When the commit "3b0110193e36b317207909163d0a582f6f568cf8" is pushed to the branch "master"
    Then the tide should be created

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

  Scenario: When a push occurs, then it should start the tide
    Given I have a flow with the following configuration:
    """
    tasks: [{build: {services: []}}]
    filter: 'code_reference.branch == "master" || "Ready for QA" in pull_request.labels'
    """
    When the commit "3b0110193e36b317207909163d0a582f6f568cf8" is pushed to the branch "master"
    Then the tide should be created
