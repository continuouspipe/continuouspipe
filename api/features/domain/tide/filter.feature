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

  Scenario: When a push occurs, then it should start the tide
    Given I have a flow with the following configuration:
    """
    tasks: [{build: {services: []}}]
    filter: 'code_reference.branch == "master"'
    """
    When the commit "3b0110193e36b317207909163d0a582f6f568cf8" is pushed to the branch "master"
    Then the tide should be created

  Scenario: Do not start when the filter do not match the branch name
    Given I have a flow with the following configuration:
    """
    tasks: [{build: {services: []}}]
    filter: 'code_reference.branch == "master"'
    """
    When the commit "3b0110193e36b317207909163d0a582f6f568qwe" is pushed to the branch "feature"
    Then the tide should not be created
