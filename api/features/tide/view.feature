Feature:
  In order to display tide information
  As a developer
  I need to be able to have a view representation of the tide

  Background:
    Given there is 1 application images in the repository

  Scenario: Tide is created
    When a tide is created with just a build task
    Then a tide view representation should have be created
    And the tide is represented as pending

  Scenario: Tide is running
    When a tide is started with a build task
    Then the tide is represented as running

  Scenario: Tide is failed
    When a tide is started
    And the tide failed
    Then the tide is represented as failed

  Scenario: Tide succeed
    When a tide is started
    And the tide is successful
    Then the tide is represented as successful

  @smoke
  Scenario: Tide timing is tracked
    When the current datetime is "2016-04-06T15:00:00Z"
    And a tide is created with just a build task
    And the current datetime is "2016-04-06T16:00:00Z"
    And the tide starts
    And the current datetime is "2016-04-06T17:00:00Z"
    And the tide is successful
    Then the tide creation date should be "2016-04-06T15:00:00Z"
    Then the tide start date should be "2016-04-06T16:00:00Z"
    And the tide finish date should be "2016-04-06T17:00:00Z"
