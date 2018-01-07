Feature:
  In order to keep the flows going
  The tides should automatically timeout

  Background:
    Given there is 1 application images in the repository
    And I have a flow with the following configuration:
    """
    tasks: [{build: {services: []}}]
    """

  Scenario: The timed out tides should be automatically failed when a tide is created
    Given the tide "00000000-0000-0000-0000-000000000000" is running and timed out
    When a tide is created
    And the delayed messages are received
    Then the tide "00000000-0000-0000-0000-000000000000" should be failed

  Scenario: Planned timeout spotting
    When a tide is started
    Then the spot timed out tides command should be scheduled
