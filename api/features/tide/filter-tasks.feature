Feature:
  In order to skip some tasks in some cases
  As a developer
  I want to be able to filter the tasks based on expressions

  Scenario: The configuration fails we the evaluation of a filter is not a boolean
    Given I have a flow with the following configuration:
    """
    tasks:
        - build:
              services: []
          filter:
              expression: foo.bar
    """
    When a tide is started
    Then the tide should be failed

  Scenario: I can run a task only if the tide branch match a given name
    Given I have a flow with the following configuration:
    """
    tasks:
        - run:
              providerName: foo
              image: busybox
              commands:
                  - foo
          filter:
              expression: codeReference.branch == 'master'
        - deploy:
              providerName: foo
              services: []
    """
    When a tide is started for the branch "feature"
    Then the deploy task should be started

  Scenario: I can run a task only if the tide branch match a given name
    Given I have a flow with the following configuration:
    """
    tasks:
        - run:
              providerName: foo
              image: busybox
              commands:
                  - foo
          filter:
              expression: codeReference.branch == 'master'
        - deploy:
              providerName: foo
              services: []
    """
    When a tide is started for the branch "master"
    Then the run task should be running

