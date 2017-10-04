Feature:
  In order to understand what I'm billed for
  As a finance or manager
  I want to know how much my teams are using in terms of builds and resources

  Background:
    Given there is a user "samuel"
    And the team "first-project" exists
    And the team "second-project" exists
    And the user "samuel" is "USER" of the team "first-project"
    And the user "samuel" is "USER" of the team "second-project"
    And I have a flow with UUID "00000000-0000-0000-0000-000000000000" in the team "first-project"
    And I have a flow with UUID "00000000-0000-0000-0000-111111111111" in the team "second-project"
    And I have a flow with UUID "00000000-0000-0000-0000-222222222222" in the team "second-project"

  Scenario: Tides usage across flows and teams
    Given I am authenticated as "samuel"
    And there is the following tides:
      | datetime             | flow_uuid                            | status  |
      | 2017-08-01T19:00:00Z | 00000000-0000-0000-0000-000000000000 | success |
      | 2017-08-01T19:00:00Z | 00000000-0000-0000-0000-111111111111 | success |
      | 2017-08-02T19:00:00Z | 00000000-0000-0000-0000-222222222222 | success |
      | 2017-08-02T19:01:00Z | 00000000-0000-0000-0000-222222222222 | success |
      | 2017-08-02T19:00:00Z | 00000000-0000-0000-0000-333333333333 | success |
    When I request the usage of the teams "first-project,second-project" from the "2017-08-01T00:00:00Z" to "2017-09-01T00:00:00Z" with a "P1D" interval
    Then I should see that on the "2017-08-01" the flow "00000000-0000-0000-0000-000000000000" from the team "first-project" used 1 tide
    And I should see that on the "2017-08-01" the flow "00000000-0000-0000-0000-111111111111" from the team "second-project" used 1 tide
    And I should see that on the "2017-08-01" the flow "00000000-0000-0000-0000-222222222222" from the team "second-project" used 0 tide
    And I should see that on the "2017-08-02" the flow "00000000-0000-0000-0000-222222222222" from the team "second-project" used 2 tide
    And I should see that on the "2017-08-02" the flow "00000000-0000-0000-0000-111111111111" from the team "second-project" used 0 tide
    And I should see that on the "2017-08-03" the flow "00000000-0000-0000-0000-000000000000" from the team "first-project" used 0 tide

  Scenario: Resources usage across flows and teams
    Given I am authenticated as "samuel"
    And the following resource usage history entry have been saved:
      | datetime             | flow_uuid                            | environment_identifier | requests_cpu | requests_memory | limits_cpu | limits_memory |
      | 2017-07-29T12:00:00Z | 00000000-0000-0000-0000-000000000000 | master                 | 100m         | 1Gi             | 200m       | 1500Mi        |
      | 2017-08-02T12:00:00Z | 00000000-0000-0000-0000-000000000000 | master                 | 200m         | 2Gi             | 400m       | 3Gi           |
      | 2017-08-07T12:00:00Z | 00000000-0000-0000-0000-111111111111 | master                 | 600m         | 10Gi            | 800m       | 15Gi          |
    When I request the usage of the teams "first-project,second-project" from the "2017-08-01T00:00:00Z" to "2017-09-01T00:00:00Z" with a "P1D" interval
    Then I should see that on the "2017-08-01" the flow "00000000-0000-0000-0000-000000000000" from the team "first-project" used "200m" of cpu
    And I should see that on the "2017-08-01" the flow "00000000-0000-0000-0000-000000000000" from the team "first-project" used "1500Mi" of memory
    And I should see that on the "2017-08-05" the flow "00000000-0000-0000-0000-000000000000" from the team "first-project" used "400m" of cpu
    And I should see that on the "2017-08-05" the flow "00000000-0000-0000-0000-000000000000" from the team "first-project" used "3Gi" of memory
    And I should see that on the "2017-08-05" the flow "00000000-0000-0000-0000-111111111111" from the team "second-project" used "0" of cpu
    And I should see that on the "2017-08-05" the flow "00000000-0000-0000-0000-111111111111" from the team "second-project" used "0" of memory
    And I should see that on the "2017-08-07" the flow "00000000-0000-0000-0000-111111111111" from the team "second-project" used "800m" of cpu
    And I should see that on the "2017-08-07" the flow "00000000-0000-0000-0000-111111111111" from the team "second-project" used "15Gi" of memory
