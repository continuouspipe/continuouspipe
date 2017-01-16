Feature:
  In order to be transparent and flexible
  As a system
  I want to handle the various errors

  Scenario: It fails the task if the build request fails
    Given there is 1 application images in the repository
    And the build request will fail
    When a build task is started
    Then the build task should be failed

  Scenario: It displays the error
    Given there is 1 application images in the repository
    And the build request will fail with the reason "The request went wrong apparently"
    When a build task is started
    Then the build task should be failed
    And a log containing "The request went wrong apparently" should be created
