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

  Scenario: Retry failed API calls
    Given the "api_retry_count" parameter is set to "4"
    And the builder API returns "500" HTTP status code 4 times
    When I send a build request
    Then I should see the build call as successful

  Scenario: Limit the number of retried API calls
    Given the "api_retry_count" parameter is set to "4"
    And the builder API returns "500" HTTP status code 5 times
    When I send a build request
    Then I should see the build call as failed

  Scenario: Record the number of failed API calls in StatsD
    Given the "api_retry_count" parameter is set to "4"
    And the builder API returns "500" HTTP status code 3 times
    When I send a build request
    Then I should see the metrics published as below:
      | metric                          | value |
      | river.outgoing.builder.success  | 1     |
      | river.outgoing.builder.failure  | 3     |
