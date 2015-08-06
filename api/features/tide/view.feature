Feature:
  In order to display tide informations
  As a developer
  I need to be able to have a view reprensentation of the tide

  Scenario:
    When a tide is created
    Then a tide view representation should have be created
    And the tide is represented as pending

  Scenario:
    When a tide is started
    Then the tide is represented as running

  Scenario:
    When a tide is started
    And the tide failed
    Then the tide is represented as failed
