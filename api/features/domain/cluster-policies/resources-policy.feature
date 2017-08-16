Feature:
  In order to ensure deployed containers all have resource requests and limits
  As a DevOps engineer
  I want to be able to specify default and maximum resource requests

  Background:
    Given the team "my-team" exists
    And there is a user "samuel"
    And the user "samuel" is "ADMIN" of the team "my-team"
    And I have a flow with UUID "00000000-0000-0000-0000-000000000000" in the team "my-team"

  Scenario: It adds the default resources to my deployed containers
    Given the cluster "flex" of the team "my-team" have the following policies:
    """
    [
      {
        "name": "resources",
        "configuration": {
          "default-cpu-request": "100m",
          "default-cpu-limit": "250m",

          "default-memory-request": "250Mi",
          "default-memory-limit": "300Mi"
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
                services:
                    app:
                        specification:
                            source:
                                image: foo/bar
    """
    When a tide is started for the branch "master"
    And the component "app" should request "100m" of CPU
    And the component "app" should be limited to "250m" of CPU
    And the component "app" should request "250Mi" of memory
    And the component "app" should be limited to "300Mi" of memory

  Scenario: It adds the default resources to my run containers
    Given the cluster "flex" of the team "my-team" have the following policies:
    """
    [
      {
        "name": "resources",
        "configuration": {
          "default-cpu-request": "100m",
          "default-cpu-limit": "250m",
          "default-memory-request": "250Mi",
          "default-memory-limit": "300Mi"
        }
      },
      {"name": "default"}
    ]
    """
    Given the team "my-team" have the credentials of a cluster "foo"
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        my_task:
            run:
                image: ubuntu
                commands: [ 'echo OK' ]
    """
    When a tide is started for the branch "master"
    And the component "run-my-task" should request "100m" of CPU
    And the component "run-my-task" should be limited to "250m" of CPU
    And the component "run-my-task" should request "250Mi" of memory
    And the component "run-my-task" should be limited to "300Mi" of memory
