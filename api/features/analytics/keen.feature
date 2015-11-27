Feature:
  In order to have insights on the ongoing platform
  As a decider
  I need to have access to some metrics

  Scenario: An event is sent to keen when a tide is failing
    Given a tide is started with a build task
    When the tide failed
    Then an event should be sent to keen in the collection "tides"

  Scenario: An event is sent to keen when a tide is succeed
    Given a tide is started with a build task
    When the tide is successful
    Then an event should be sent to keen in the collection "tides"
