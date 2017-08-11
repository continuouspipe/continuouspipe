Feature:
  In order to have environments following a rule
  As a DevOps engineer
  I want to be able to specify the mandatory environment prefix

  Background:
    Given the team "my-team" exists
    And there is a user "samuel"
    And the user "samuel" is "ADMIN" of the team "my-team"
    And I have a flow with UUID "00000000-0000-0000-0000-000000000000" in the team "my-team"

  Scenario: Environment is deployed when matching policy
    Given the cluster "flex" of the team "my-team" have the following policies:
    """
    [
      {
        "name": "environment",
        "configuration": {
          "prefix": "abc-123"
        }
      }
    ]
    """
    Given the team "my-team" have the credentials of a cluster "foo"
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        my_deployment:
            deploy:
                cluster: flex
                environment:
                    name: "'abc-123-master'"
                services:
                    app:
                        specification:
                            source:
                                image: foo/bar
    """
    When a tide is started for the branch "master"
    Then the name of the deployed environment should be "abc-123-master"

  Scenario: Deployment fails if policy is not matched
    Given the cluster "flex" of the team "my-team" have the following policies:
    """
    [
      {
        "name": "environment",
        "configuration": {
          "prefix": "abc-123"
        }
      }
    ]
    """
    Given the team "my-team" have the credentials of a cluster "foo"
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        my_deployment:
            deploy:
                cluster: flex
                environment:
                    name: "'query-master'"
                services:
                    app:
                        specification:
                            source:
                                image: foo/bar
    """
    When a tide is started for the branch "master"
    Then the tide should be failed
