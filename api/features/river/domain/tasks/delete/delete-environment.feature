Feature:
  In order to clean-up resources after I used them
  As a DevOps engineer
  I want my pipeline to delete an environment after it has been created

  Scenario: It deletes an environment after creating it
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        infrastructure:
            deploy:
                cluster: foo
                environment:
                    name: '"app-" ~ code_reference.branch'
                services:
                    foo:
                        specification:
                            source:
                                image: foo

        cleanup:
            delete:
                cluster: foo
                environment:
                    name: '"app-" ~ code_reference.branch'
    """
    When a tide is started for the branch "master"
    And the deployment succeed
    Then the environment "app-master" should have been deleted from the cluster "foo"

  Scenario: It deletes default environments
    Given I have a flow with UUID "00000000-0000-0000-0000-000000000000"
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        infrastructure:
            deploy:
                cluster: foo
                services:
                    foo:
                        specification:
                            source:
                                image: foo

        cleanup:
            delete:
                cluster: foo
    """
    When a tide is started for the branch "master"
    And the deployment succeed
    Then the environment "00000000-0000-0000-0000-000000000000-master" should have been deleted from the cluster "foo"

  Scenario: It deletes the right environment with defaults
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    defaults:
        cluster: foo
        environment:
            name: '"app-" ~ code_reference.branch'
    tasks:
        infrastructure:
            deploy:
                services:
                    foo:
                        specification:
                            source:
                                image: foo

        cleanup:
            delete: ~
    """
    When a tide is started for the branch "master"
    And the deployment succeed
    Then the environment "app-master" should have been deleted from the cluster "foo"

  Scenario: It only deletes the right environment
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    defaults:
        cluster: foo
        environment:
            name: '"app-" ~ code_reference.branch'

    tasks:
        infrastructure:
            deploy:
                services:
                    foo:
                        specification:
                            source:
                                image: foo


        test_infrastructure:
            deploy:
                environment:
                    name: '"app-test-" ~ code_reference.branch'
                services:
                    bar:
                        specification:
                            source:
                                image: foo

        cleanup:
            delete:
                environment:
                    name: '"app-test-" ~ code_reference.branch'
    """
    When a tide is started for the branch "master"
    And the deployment succeed
    And the second deploy succeed
    Then the environment "app-test-master" should have been deleted from the cluster "foo"
    And the environment "app-master" should not have been deleted from the cluster "foo"

  Scenario: When something goes wrong, it displays it...
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    defaults:
        cluster: foo
        environment:
            name: '"app-" ~ code_reference.branch'
    tasks:
        infrastructure:
            deploy:
                services:
                    foo:
                        specification:
                            source:
                                image: foo

        cleanup:
            delete: ~
    """
    And the environment deletion will fail with the message "Environment is not found"
    When a tide is started for the branch "master"
    And the deployment succeed
    Then the task "cleanup" should be "failed"
    And a log containing "Environment is not found" should be created
