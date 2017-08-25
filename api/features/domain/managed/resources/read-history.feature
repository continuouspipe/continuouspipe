Feature:
  In order to know what resources am I using
  As a user
  I want to be able to get my resource usage per hour

  Background:
    Given the team "samuel" exists
    And there is a user "samuel"
    And the user "samuel" is "USER" of the team "samuel"
    And I have a flow with UUID "94b58d8e-7c2f-11e7-b19c-0a580a8405ef" in the team "samuel"

  Scenario: I can't get flow usage without being authenticated
    When I request the resource usage of the flow "94b58d8e-7c2f-11e7-b19c-0a580a8405ef" from the "2017-08-01T00:00:00Z" to "2017-09-01T00:00:00Z" with a "P1D" interval
    Then I should be told that I am not authenticated

  Scenario: I can't get flow usage without access to the flow
    Given I am authenticated as "somebody"
    When I request the resource usage of the flow "94b58d8e-7c2f-11e7-b19c-0a580a8405ef" from the "2017-08-01T00:00:00Z" to "2017-09-01T00:00:00Z" with a "P1D" interval
    Then I should be told that I don't have the permissions

  Scenario: I can get my flow usage in the last 30 days
    Given I am authenticated as "samuel"
    And the following resource usage history entry have been saved:
      | datetime             | flow_uuid                            | environment_identifier | requests_cpu | requests_memory | limits_cpu | limits_memory |
      | 2017-07-29T12:00:00Z | 94b58d8e-7c2f-11e7-b19c-0a580a8405ef | master                 | 100m         | 1Gi             | 200m       | 1500Mi        |
      | 2017-08-02T12:00:00Z | 94b58d8e-7c2f-11e7-b19c-0a580a8405ef | master                 | 200m         | 2Gi             | 400m       | 3Gi           |
      | 2017-08-03T12:00:00Z | 94b58d8e-7c2f-11e7-b19c-0a580a8405ef | feature-branch         | 100m         | 1Gi             | 200m       | 1500Mi        |
      | 2017-08-03T14:00:00Z | 94b58d8e-7c2f-11e7-b19c-0a580a8405ef | feature-branch         | 0m           | 0               | 0m         | 0             |
      | 2017-08-05T12:00:00Z | 94b58d8e-7c2f-11e7-b19c-0a580a8405ef | master                 | 100m         | 1Gi             | 200m       | 1500Mi        |
      | 2017-08-05T12:00:00Z | 94b58d8e-0000-0000-0000-0a580a8405ef | some-noise             | 2            | 5Gi             | 3          | 10Gi          |
      | 2017-08-07T12:00:00Z | 94b58d8e-7c2f-11e7-b19c-0a580a8405ef | master                 | 600m         | 10Gi            | 800m       | 15Gi          |
      | 2017-08-08T12:00:00Z | 94b58d8e-7c2f-11e7-b19c-0a580a8405ef | master                 | 300m         | 1Gi             | 300m       | 800Mi        |
    When I request the resource usage of the flow "94b58d8e-7c2f-11e7-b19c-0a580a8405ef" from the "2017-08-01T00:00:00Z" to "2017-09-01T00:00:00Z" with a "P1D" interval
    Then I should see the following resource usage:
    | datetime   | cpu       | memory       |
    | 2017-08-01 | 200m      | 1500Mi       |
    | 2017-08-02 | 400m      | 3000Mi       |
    | 2017-08-03 | 600m      | 4500Mi       |
    | 2017-08-04 | 400m      | 3000Mi       |
    # Because on the 5th, the max is what was there on the 4th:
    | 2017-08-05 | 400m      | 3000Mi       |
    | 2017-08-06 | 200m      | 1500Mi       |
    | 2017-08-07 | 800m      | 15Gi         |
    | 2017-08-08 | 800m      | 15Gi         |
    | 2017-08-09 | 300m      | 800Mi        |
    | 2017-08-10 | 300m      | 800Mi        |
    | 2017-08-11 | 300m      | 800Mi        |
    | 2017-08-12 | 300m      | 800Mi        |
