Feature:
  In order to run migrations or fixtures load
  As a developer
  I want to run commands in containers running in the deployed environment

  Scenario:
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        testing:
            run:
                providerName: foo
                commands:
                    - echo testing
                    - sleep 10
                    - echo done
                image: busybox
    """
    When a tide is started
    Then the component "testing" should be deployed as attached
    And the component "testing" should be deployed as not scaling
