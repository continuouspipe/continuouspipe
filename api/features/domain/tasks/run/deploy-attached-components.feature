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
    Then the component "testing" should be deployed as attached
    And the component "testing" should be deployed as not scaling

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
    Then the component "testing-command" should be deployed
