Feature:
  In order to be transparent and flexible
  As a system
  I want to handle the various errors

  Scenario: It fails the task if the build request fails
    Given there is 1 application images in the repository
    And the build request will fail
    When a build task is started
    Then the build task should be failed
