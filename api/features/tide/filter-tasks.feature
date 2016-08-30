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

  @smoke
  Scenario: I can run a task only if the tide branch match a given name
    Given I have a flow with the following configuration:
    """
    tasks:
        - run:
              cluster: foo
              image: busybox
              commands:
                  - foo
          filter:
              expression: code_reference.branch == 'master'
        - deploy:
              cluster: foo
              services: []
    """
    When a tide is started for the branch "feature"
    Then the deploy task should be started

  Scenario: I can run a task only if the tide branch match a given name
    Given I have a flow with the following configuration:
    """
    tasks:
        - run:
              cluster: foo
              image: busybox
              commands:
                  - foo
          filter:
              expression: code_reference.branch == 'master'
        - deploy:
              cluster: foo
              services: []
    """
    When a tide is started for the branch "master"
    Then the run task should be running

  Scenario: I can run a run task only if a given service was created
    Given I have a flow with the following configuration:
    """
    tasks:
        deployment:
            deploy:
                cluster: foo
                services:
                    mysql:
                        specification:
                            source:
                                image: mysql
        fixtures:
            run:
                cluster: foo
                image: busybox
                commands:
                    - foo
            filter:
                expression: tasks.deployment.services.mysql.created
    """
    And a tide is started
    When the service mysql was created
    And the deployment succeed
    Then the run task should be running

  Scenario: I can run a run task only if a given service was created
    Given I have a flow with the following configuration:
    """
    tasks:
        deployment:
            deploy:
                cluster: foo
                services:
                    mysql:
                        specification:
                            source:
                                image: mysql
        fixtures:
            run:
                cluster: foo
                image: busybox
                commands:
                    - foo
            filter:
                expression: tasks.deployment.services.mysql.created
    """
    And a tide is started
    When the service mysql was not created
    And the deployment succeed
    Then the tide should be successful

  Scenario: The tide should fail in an expression refer to a non-existing service of a deploy task
    Given I have a flow with the following configuration:
    """
    tasks:
        deployment:
            deploy:
                cluster: foo
                services:
                    mysql:
                        specification:
                            source:
                                image: mysql
        fixtures:
            run:
                cluster: foo
                image: busybox
                commands:
                    - foo
            filter:
                expression: tasks.deployment.services.bar.created
    """
    When a tide is started
    And the deployment succeed
    Then the tide should be failed

  Scenario: The tide should fail in an expression refer to a non-existing task
    Given I have a flow with the following configuration:
    """
    tasks:
        fixtures:
            run:
                cluster: foo
                image: busybox
                commands:
                    - foo
            filter:
                expression: tasks.deployment.services.mysql.created
    """
    When a tide is started
    Then the tide should be failed

  Scenario: Filtering on pull request without pull-request
    Given I have a flow with the following configuration:
    """
    tasks:
        images:
            build:
                services: []

        environment:
            deploy:
                cluster: foo
                services:
                    mysql:
                        specification:
                            source:
                                image: mysql

            filter:
                expression: "code_reference.branch == 'master' or 'Ready for QA' in pull_request.labels"
    """
    When a tide is started for the branch "master"
    Then the build task succeed
    And the deploy task should be started

  Scenario: Filtering on pull request without pull-request
    Given I have a flow with the following configuration:
    """
    tasks:
        images:
            build:
                services: []

        environment:
            deploy:
                cluster: foo
                services:
                    mysql:
                        specification:
                            source:
                                image: mysql

            filter:
                expression: "'Ready for QA' in pull_request.labels"
    """
    When a tide is started for the branch "master"
    Then the build task succeed
    And the deploy task should not be started

  Scenario: Filtering on pull request's labels
    Given I have a flow with the following configuration:
    """
    tasks:
        images:
            build:
                services: []

        environment:
            deploy:
                cluster: foo
                services:
                    mysql:
                        specification:
                            source:
                                image: mysql

            filter:
                expression: "'Ready for QA' in pull_request.labels"
    """
    And the pull request #1 have the label "Ready for QA"
    When the pull request #1 is labeled
    And the tide starts
    Then the tide should be created
    And the build task succeed
    And the deploy task should be started

  Scenario: Filtering on pull request's labels
    Given I have a flow with the following configuration:
    """
    tasks:
        images:
            build:
                services: []

        environment:
            deploy:
                cluster: foo
                services:
                    mysql:
                        specification:
                            source:
                                image: mysql

            filter:
                expression: "'Ready for QA' in pull_request.labels"
    """
    And the pull request #1 have the label "Ready for QA"
    When the pull request #1 is synchronized
    And the tide starts
    Then the tide should be created
    And the build task succeed
    And the deploy task should be started

  Scenario: Filtering on pull request's labels
    Given I have a flow with the following configuration:
    """
    tasks:
        images:
            build:
                services: []

        environment:
            deploy:
                cluster: foo
                services:
                    mysql:
                        specification:
                            source:
                                image: mysql

            filter:
                expression: "'Ready for QA' in pull_request.labels"
    """
    And the pull request #1 have the label "Ready for QA"
    And the pull-request #1 contains the tide-related commit
    When a tide is started for the branch "foo"
    And the build task succeed
    Then the deploy task should be started

  Scenario: I can refer to a skipped deployed task and it resolves to false
    Given I have a flow with the following configuration:
    """
    tasks:
        deployment:
            deploy:
                cluster: foo
                services:
                    mysql:
                        specification:
                            source:
                                image: mysql

            filter:
                expression: 'code_reference.branch == "master"'

        fixtures:
            run:
                cluster: foo
                image: busybox
                commands:
                    - foo

            filter:
                expression: tasks.deployment.services.mysql.created
    """
    When a tide is started for the branch "my/feature"
    Then the tide should be successful
