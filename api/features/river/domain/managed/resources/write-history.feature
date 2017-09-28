Feature:
  In order to control what a user is using in terms of resources
  As a ContinuousPipe owner
  I want to be able to receive notifications about the resource usages

  Scenario: Cannot save usage without token
    When the following POST request is sent to "/managed/resources":
    """
    {}
    """
    Then I should be told that I am not authenticated

  Scenario: Cannot save usage as a non-system user
    Given I am authenticated as "samuel"
    When the following POST request is sent to "/managed/resources":
    """
    {}
    """
    Then I should be told that I don't have the permissions

  @smoke
  Scenario: I receive and store resource for a flow
    Given I have a flow with UUID "00000000-0000-0000-0000-000011112222"
    And I am authenticated with the "ROLE_RESOURCE_USAGE_CREATOR" role
    When the following POST request is sent to "/managed/resources":
    """
    {
      "namespace":{
        "name":"00000000-0000-0000-0000-000011112222-master",
        "labels":{
          "continuous-pipe-environment":"00000000-0000-0000-0000-000011112222-master",
          "created-by":"continuous-pipe",
          "flow":"00000000-0000-0000-0000-000011112222"
        }
      },
      "limits":{
        "cpu":"500m",
        "memory":"500Mi"
      },
      "requests":{
        "cpu":"25m",
        "memory":"50Mi"
      }
    }
    """
    Then I should be told that the resource has been created
    And a resource usage entry for the environment "00000000-0000-0000-0000-000011112222-master" in the flow "00000000-0000-0000-0000-000011112222" should have been saved

  Scenario: No data means 0
    Given I have a flow with UUID "00000000-0000-0000-0000-000011112222"
    And I am authenticated with the "ROLE_RESOURCE_USAGE_CREATOR" role
    When the following POST request is sent to "/managed/resources":
    """
    {
      "namespace":{
        "name":"00000000-0000-0000-0000-000011112222-master",
        "labels":{
          "continuous-pipe-environment":"00000000-0000-0000-0000-000011112222-master",
          "created-by":"continuous-pipe",
          "flow":"00000000-0000-0000-0000-000011112222"
        }
      },
      "limits":{
        "cpu":"1"
      },
      "requests":{
        "memory":"50Mi"
      }
    }
    """
    Then I should be told that the resource has been created
    And a resource usage entry for the environment "00000000-0000-0000-0000-000011112222-master" in the flow "00000000-0000-0000-0000-000011112222" should have been saved
    And the resource usage entry for the environment "00000000-0000-0000-0000-000011112222-master" in the flow "00000000-0000-0000-0000-000011112222" should have a memory limit of 0
    And the resource usage entry for the environment "00000000-0000-0000-0000-000011112222-master" in the flow "00000000-0000-0000-0000-000011112222" should have a CPU limit of 1
    And the resource usage entry for the environment "00000000-0000-0000-0000-000011112222-master" in the flow "00000000-0000-0000-0000-000011112222" should have a memory request of 50Mi
    And the resource usage entry for the environment "00000000-0000-0000-0000-000011112222-master" in the flow "00000000-0000-0000-0000-000011112222" should have a CPU request of 0
