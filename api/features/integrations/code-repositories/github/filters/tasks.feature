Feature:
  In order to run tides depending on a PR status
  As a developer
  I want to be able to filter tasks based on the pull-request status

  Background:
    Given there is 1 application images in the repository

  Scenario: Filtering on pull request without pull-request
    Given I have a flow with the following configuration:
    """
    tasks:
        images:
            build: ~

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
            build: ~

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
            build: ~

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
            build: ~

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
            build: ~

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
    And the GitHub pull-request #1 contains the tide-related commit
    When a tide is started for the branch "foo"
    And the build task succeed
    Then the deploy task should be started
