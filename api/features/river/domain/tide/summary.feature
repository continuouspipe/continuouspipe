Feature:
  In order to have an overview of the running or ran tide
  As a developer
  I want to be able to get the summary of the tide and all the useful informations from it

  Background:
    Given I am authenticated as "samuel"
    And the team "samuel" exists
    And I have a flow with UUID "00000000-0000-0000-0000-000000000000" in the team "samuel"
    And the user "samuel" is "USER" of the team "samuel"
    And there is 1 application images in the repository

  Scenario: I can get the summary of a given tide
    Given a tide is created
    And the tide failed
    When I ask the summary of the tide
    Then I should see that the tide is failed

  Scenario: If there's a deploy task, I should see the deployed components
    Given a tide is started with a deploy task
    And the service "foo" was created with the public address "1.2.3.4"
    And the deployment succeed
    When I ask the summary of the tide
    Then I should see in the list the following deployed services:
    | name | address |
    | foo  | 1.2.3.4 |

  Scenario: If the tide is running, it should return the current running task's log
    And a tide is started with a build and deploy task
    When I ask the summary of the tide
    Then I should see that the tide is running
    And I should see that the current task is the build task

  Scenario: If the tide is running, it should return the current running task's log
    Given a tide is started with a build and deploy task
    And the build succeed
    When I ask the summary of the tide
    Then I should see that the tide is running
    And I should see that the current task is the deploy task

  Scenario: No relation
    Given a tide is started
    When I ask the external relations
    Then I should see no external relation

  Scenario: It also returns the unknown endpoints as components
    Given a tide is started with a deploy task
    And the service "zed" was created with the following public endpoints:
      | name     | address | ports  |
      | zed      | 1.2.3.4 | 80     |
      | zedhttps | 9.8.7.6 | 80,443 |
    And the deployment succeed
    When I ask the summary of the tide
    Then I should see in the list the following deployed services:
      | name     | address |
      | zed      | 1.2.3.4 |
      | zedhttps | 9.8.7.6 |

  Scenario: If there's a deploy task, I should see the environment name
    Given a tide is started for the branch "my-feature" with a deploy task
    And the deployment succeed
    When I ask the summary of the tide
    Then I should see the "00000000-0000-0000-0000-000000000000-my-feature" environment

  Scenario: If there's a deploy task, I should see the environment name even if deployment failed
    Given a tide is started for the branch "my-feature" with a deploy task
    When I ask the summary of the tide
    Then I should see the "00000000-0000-0000-0000-000000000000-my-feature" environment

  Scenario: It returns the deployed services of all the deployment tasks
    Given a tide is started with the following configuration:
    """
    tasks:
        database:
            deploy:
                cluster: fake/foo
                services:
                    database:
                        specification:
                            source:
                                image: foo:bar

        app:
            deploy:
                cluster: fake/foo
                services:
                    app:
                        specification:
                            source:
                                image: foo:bar
    """
    And the first deploy succeed
    And the second deploy succeed with the following public endpoints:
      | name     | address | ports  |
      | app      | 1.2.3.4 | 80     |
    When I ask the summary of the tide
    Then I should see in the list the following deployed services:
      | name     | address |
      | app      | 1.2.3.4 |
