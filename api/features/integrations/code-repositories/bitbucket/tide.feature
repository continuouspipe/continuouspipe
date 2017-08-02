Feature:
  In order to run deployments
  As a BitBucket user
  I want to be able to start tides

  Background:
    Given I am authenticated as "samuel"
    And the team "samuel" exists
    And the user "samuel" is "USER" of the team "samuel"

  Scenario: Create a tide from the code repository's configuration
    Given I have a flow with a BitBucket repository "example" owned by user "foo"
    And there is the add-on installed for the BitBucket repository "example" owned by user "foo"
    And there is a "continuous-pipe.yml" file in my BitBucket repository that contains:
    """
    tasks:
        images:
            build: {services: []}
    """
    When I send a tide creation request for branch "master" and commit "123456"
    Then the tide should be created
    And the tide should have the task "images"

  Scenario: Create a tide from a repository with space in the name
    Given I have a flow with a BitBucket repository named "My example" with slug "my-example" and owned by user "foo"
    And there is the add-on installed for the BitBucket repository "example" owned by user "foo"
    And there is a "continuous-pipe.yml" file in the BitBucket repository "my-example" owned by "foo" that contains:
    """
    tasks:
        images:
            build: {services: []}
    """
    When I send a tide creation request for branch "master" and commit "123456"
    Then the tide should be created
    And the tide should have the task "images"

  Scenario: The first status is stopped when I just push something
    Given I have a flow with a BitBucket repository "example" owned by user "foo"
    And there is the add-on installed for the BitBucket repository "example" owned by user "foo"
    When I push the anonymous commit "12345" to the branch "master" of the BitBucket repository "example" owned by user "foo"
    Then the tide should be created

  Scenario: I can list branches from a BitBucket account in order to auto complete when manually creating a tide
    Given I have a flow "00000000-0000-0000-0000-000000000000" with a Bitbucket repository "my-example" owned by "samuel"
    And there is the add-on installed for the BitBucket repository "my-example" owned by user "samuel"
    And the following branches exist in the bitbucket repository with slug "my-example" for user "samuel":
      | name    |
      | master  |
      | develop |
    When I request the account's branches for the flow "00000000-0000-0000-0000-000000000000"
    Then I should see the following branches:
      | name |
      | master  |
      | develop  |
