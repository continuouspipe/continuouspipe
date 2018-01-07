Feature:
  In order to have a feedback on deployed environments
  As a developer
  I want to have the environment addresses commented on my pull-requests

  Scenario: An environment created from an external pull-request should be labelled as it
    Given I have a flow with the following configuration:
    """
    tasks:
        - {build: {services: []}}
    """
    When the pull request #1 is opened with head "feature/dc-labels" from another repository labelled "sroze"
    Then the tide should be created
