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
    Given I have a flow with UUID "94b58d8e-7c2f-11e7-b19c-0a580a8405ef"
    And I am authenticated with the "ROLE_RESOURCE_USAGE_CREATOR" role
    When the following POST request is sent to "/managed/resources":
    """
    {
      "namespace":{
        "name":"94b58d8e-7c2f-11e7-b19c-0a580a8405ef-master",
        "labels":{
          "continuous-pipe-environment":"94b58d8e-7c2f-11e7-b19c-0a580a8405ef-master",
          "created-by":"continuous-pipe",
          "flow":"94b58d8e-7c2f-11e7-b19c-0a580a8405ef"
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
    And a resource usage entry for the environment "94b58d8e-7c2f-11e7-b19c-0a580a8405ef-master" in the flow "94b58d8e-7c2f-11e7-b19c-0a580a8405ef" should have been saved
