Feature:
  In order to understand how much of I consume
  As a user
  I want to be able to get a reporting of my tide usage

  Background:
    Given the team "samuel" exists
    And there is a user "samuel"
    And the user "samuel" is "USER" of the team "samuel"
    And I have a flow with UUID "00000000-0000-0000-0000-000000000000" in the team "samuel"

  Scenario: I can't get flow usage without being authenticated
    When I request the tide usage of the flow "00000000-0000-0000-0000-000000000000" from the "2017-08-01T00:00:00Z" to "2017-09-01T00:00:00Z" with a "P1D" interval
    Then I should be told that I am not authenticated

  Scenario: I can't get flow usage without access to the flow
    Given I am authenticated as "somebody"
    When I request the tide usage of the flow "00000000-0000-0000-0000-000000000000" from the "2017-08-01T00:00:00Z" to "2017-09-01T00:00:00Z" with a "P1D" interval
    Then I should be told that I don't have the permissions

  Scenario: I can get my flow usage in the last 30 days
    Given I am authenticated as "samuel"
    And there is the following tides:
      | datetime             | flow_uuid                            | status  |
      | 2017-07-29T12:00:00Z | 94b58d8e-7c2f-11e7-b19c-0a580a8405ef | success |
      | 2017-07-29T19:00:00Z | 00000000-0000-0000-0000-000000000000 | success |
      | 2017-08-01T19:00:00Z | 94b58d8e-7c2f-11e7-b19c-0a580a8405ef | success |
      | 2017-08-01T19:00:00Z | 00000000-0000-0000-0000-000000000000 | success |
      | 2017-08-02T19:00:00Z | 00000000-0000-0000-0000-000000000000 | success |
      | 2017-08-02T19:00:00Z | 00000000-0000-0000-0000-000000000000 | failure |
      | 2017-08-03T19:00:00Z | 94b58d8e-7c2f-11e7-b19c-0a580a8405ef | success |
      | 2017-08-04T01:00:00Z | 00000000-0000-0000-0000-000000000000 | success |
    When I request the tide usage of the flow "00000000-0000-0000-0000-000000000000" from the "2017-08-01T00:00:00Z" to "2017-09-01T00:00:00Z" with a "P1D" interval
    Then I should see the following tide usage:
      | datetime   | tides |
      | 2017-08-01 | 1     |
      | 2017-08-02 | 2     |
      | 2017-08-03 | 0     |
      | 2017-08-04 | 1     |
