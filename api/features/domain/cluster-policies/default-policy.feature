Feature:
  In order to simplify the life of software engineers
  As a DevOps engineer
  I want to be able to specify a default cluster to be used while doing deployments and runs

  Background:
    Given the team "my-team" exists
    And there is a user "samuel"
    And the user "samuel" is "ADMIN" of the team "my-team"
    And I have a flow with UUID "00000000-0000-0000-0000-000000000000" in the team "my-team"

  Scenario: Environment is deployed when matching policy
    Given the cluster "flex" of the team "my-team" have the following policies:
    """
    [{"name": "default"}]
    """
    Given the team "my-team" have the credentials of a cluster "foo"
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        my_deployment:
            deploy:
                services:
                    app:
                        specification:
                            source:
                                image: foo/bar
    """
    When a tide is started for the branch "master"
    Then the environment should have been deployed on the cluster "flex"
