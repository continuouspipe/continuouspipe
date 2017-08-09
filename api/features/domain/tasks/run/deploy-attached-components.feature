Feature:
  In order to run migrations or fixtures load
  As a developer
  I want to run commands in containers running in the deployed environment

  Scenario: Deploy a component
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        testing:
            run:
                cluster: foo
                commands:
                    - echo testing
                    - sleep 10
                    - echo done
                image: busybox
    """
    When a tide is started
    Then the component "run-testing" should be deployed as attached
    And the component "run-testing" should be deployed as not scaling

  Scenario: The component name should be a valid DNS name
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        testing_command:
            run:
                cluster: foo
                commands:
                    - echo testing
                    - sleep 10
                    - echo done
                image: busybox
    """
    When a tide is started
    Then the component "run-testing-command" should be deployed

  Scenario: I can use persistent volumes
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        testing_command:
            run:
                cluster: foo
                commands:
                    - echo testing
                    - sleep 10
                    - echo done
                image: busybox
                volumes:
                    - type: persistent
                      name: api-volume
                      capacity: 5Gi
                volume_mounts:
                    - name: api-volume
                      mount_path: /var/lib/app
    """
    When a tide is started
    Then the component "run-testing-command" should be deployed
    And the component "run-testing-command" should have a persistent volume mounted at "/var/lib/app"
