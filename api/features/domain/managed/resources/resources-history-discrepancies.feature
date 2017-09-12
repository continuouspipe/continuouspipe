Feature:
  In order to accurately measure the environment usage if something went wrong when sending the usage information to ContinuousPipe from the cluster
  As a system
  I want to have periodic snapshots sent, and I will automatically add zero resources if I did not get any information about a given namespace within the snapshot intervals

  Background:
    Given the team "samuel" exists
    And there is a user "samuel"
    And the user "samuel" is "USER" of the team "samuel"
    And I have a flow with UUID "00000000-0000-0000-0000-000011112222" in the team "samuel"

  Scenario: It will add zero resources if no entry since for more than 12 hours
    Given the following resource usage history entry have been saved:
      | datetime             | flow_uuid                            | environment_identifier | requests_cpu | requests_memory | limits_cpu | limits_memory |
      | 2017-08-02T12:00:00Z | 00000000-0000-0000-0000-000011112222 | master                 | 200m         | 2Gi             | 400m       | 3Gi           |
      | 2017-08-02T14:00:00Z | 00000000-0000-0000-0000-000011112222 | first-feature-branch   | 100m         | 1Gi             | 200m       | 1500Mi        |
      | 2017-08-02T14:00:00Z | 00000000-0000-0000-0000-000011112222 | second-feature-branch  | 100m         | 1Gi             | 200m       | 1500Mi        |
      | 2017-08-02T15:00:00Z | 00000000-0000-0000-0000-000011112222 | second-feature-branch  | 0            | 0               | 0          | 0             |
      | 2017-08-02T18:00:00Z | 00000000-0000-0000-0000-000011112222 | master                 | 600m         | 10Gi            | 800m       | 15Gi          |
      | 2017-08-03T00:00:00Z | 00000000-0000-0000-0000-000011112222 | master                 | 300m         | 1Gi             | 300m       | 800Mi         |
      | 2017-08-03T06:00:00Z | 00000000-0000-0000-0000-000011112222 | master                 | 300m         | 1Gi             | 300m       | 800Mi         |
      | 2017-08-03T12:00:00Z | 00000000-0000-0000-0000-000011112222 | master                 | 300m         | 1Gi             | 300m       | 800Mi         |
    When I attempt to repair the resources discrepancies between "2017-08-02T00:00:00Z" and "2017-08-04T00:00:00Z" for the flow "00000000-0000-0000-0000-000011112222"
    Then a resource usage entry for the environment "first-feature-branch" in the flow "00000000-0000-0000-0000-000011112222" for the date "2017-08-03T00:00:00Z" should have been saved
    And the resource usage entry for the environment "first-feature-branch" in the flow "00000000-0000-0000-0000-000011112222" for the date "2017-08-03T00:00:00Z" should have a memory limit of 0
    And the resource usage entry for the environment "first-feature-branch" in the flow "00000000-0000-0000-0000-000011112222" for the date "2017-08-03T00:00:00Z" should have a CPU limit of 0
    And a resource usage entry for the environment "first-feature-branch" in the flow "00000000-0000-0000-0000-000011112222" for the date "2017-08-03T12:00:00Z" should not have been saved
    And a resource usage entry for the environment "second-feature-branch" in the flow "00000000-0000-0000-0000-000011112222" should not have been saved
    And a resource usage entry for the environment "master" in the flow "00000000-0000-0000-0000-000011112222" should not have been saved
