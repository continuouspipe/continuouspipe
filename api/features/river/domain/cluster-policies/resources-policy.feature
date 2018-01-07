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

  Scenario: Request resources are above limit
    Given the cluster "flex" of the team "my-team" have the following policies:
    """
    [
      {
        "name": "resources",
        "configuration": {
          "max-cpu-request": "100m",
          "max-cpu-limit": "250m",
          "max-memory-request": "250Mi",
          "max-memory-limit": "300Mi"
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

                            resources:
                                requests:
                                    cpu: 250m
                                    memory: 2Gi
                                limits:
                                    cpu: 1
                                    memory: 3Gi
    """
    When a tide is started for the branch "master"
    Then the tide should be failed
    And a log containing 'Component "app" has a requested "250m" CPU while "100m" is enforced by the cluster policy' should be created

  Scenario: Keep the same resources when within boudaries
    Given the cluster "flex" of the team "my-team" have the following policies:
    """
    [
      {
        "name": "resources",
        "configuration": {
          "default-cpu-request": "100m",
          "default-cpu-limit": "250m",
          "default-memory-request": "250Mi",
          "default-memory-limit": "300Mi",
          "max-cpu-request": "2",
          "max-cpu-limit": "4",
          "max-memory-request": "4Gi",
          "max-memory-limit": "5Gi"
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

                            resources:
                                requests:
                                    cpu: 250m
                                    memory: 2Gi
                                limits:
                                    cpu: 1
                                    memory: 3Gi
    """
    When a tide is started for the branch "master"
    Then the component "app" should request "250m" of CPU
    And the component "app" should be limited to "1" of CPU
    And the component "app" should request "2Gi" of memory
    And the component "app" should be limited to "3Gi" of memory

  Scenario: It will only add default resources where needed
    Given the cluster "flex" of the team "my-team" have the following policies:
    """
    [
      {
        "name": "resources",
        "configuration": {
          "default-cpu-request": "100m",
          "default-cpu-limit": "250m",
          "default-memory-request": "250Mi",
          "default-memory-limit": "300Mi",
          "max-cpu-request": "2",
          "max-cpu-limit": "4",
          "max-memory-request": "4Gi",
          "max-memory-limit": "5Gi"
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

                            resources:
                                requests:
                                    memory: 2Gi
                                limits:
                                    cpu: 1
    """
    When a tide is started for the branch "master"
    Then the component "app" should request "100m" of CPU
    And the component "app" should be limited to "1" of CPU
    And the component "app" should request "2Gi" of memory
    And the component "app" should be limited to "300Mi" of memory

  Scenario: It do not complain if no limit nor value
    Given the cluster "flex" of the team "my-team" have the following policies:
    """
    [
      {
        "name": "resources",
        "configuration": {
          "default-memory-request": "250Mi",
          "default-memory-limit": "300Mi",
          "max-memory-request": "4Gi",
          "max-memory-limit": "5Gi"
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

                            resources:
                                requests:
                                    memory: 2Gi
                                limits:
                                    cpu: 1
    """
    When a tide is started for the branch "master"
    Then the component "app" should not request any CPU
    And the component "app" should be limited to "1" of CPU

  Scenario: It requires requests and limits to be equals
    Given the cluster "flex" of the team "my-team" have the following policies:
    """
    [
      {
        "name": "resources",
        "configuration": {
          "memory-requests-and-limits-have-to-be-equals": "true",
          "cpu-requests-and-limits-have-to-be-equals": "true"
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

                            resources:
                                requests:
                                    memory: 2Gi
                                limits:
                                    memory: 2Gi
    """
    When a tide is started for the branch "master"
    Then the component "app" should not request any CPU
    And the component "app" should request "2Gi" of memory
    And the component "app" should be limited to "2Gi" of memory

  Scenario: It complains when requests and limits are different
    Given the cluster "flex" of the team "my-team" have the following policies:
    """
    [
      {
        "name": "resources",
        "configuration": {
          "memory-requests-and-limits-have-to-be-equals": "true",
          "cpu-requests-and-limits-have-to-be-equals": "true"
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

                            resources:
                                requests:
                                    memory: 2Gi
                                    cpu: 1
                                limits:
                                    memory: 2Gi
                                    cpu: 500m
    """
    When a tide is started for the branch "master"
    Then the tide should be failed
    And a log containing 'Component "app" need to have CPU limits (got "500m") matching CPU requests (got "1")' should be created
