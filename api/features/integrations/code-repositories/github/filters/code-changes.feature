Feature:
  In order to support monolith repositories or less-often ran tasks
  As a user
  I want to be able to run tasks or pipelines based on the changes of my code repository

  Usage:
  ```
  filter: "has_changes_for_files(['src/**'])"

  ```
  Decision tree for the return value of such `has_changes_for_files` method.

  ? Is there a previous successful tide for this branch
    No:
       ? It's the default branch?
         Yes: `true`
         No: fetches changed files from the default branch and glob matches them

    Yes:
      Checkout the changes against last commit on branch

  Background:
    Given I have a flow with the following configuration:
    """
    tasks:
        base_images:
            build:
                services: []

            filter:
                expression: 'has_changes_for_files(["docker/base/**"])'

        images:
            build:
                services: []
    """

  Scenario: No previous tide, on default branch
    When a tide is started for the branch "master"
    Then the task "base_images" should be "successful"

  Scenario: No previous tide, on non-default branch, matching changes
    Given the changes between the reference "master" and "12345" are:
      | status   | filename               |
      | modified | app/config.yml         |
      | added    | docker/base/Dockerfile |
    When a tide is started for the branch "feature/foo" and commit "12345"
    Then the task "base_images" should be "successful"

  @wip
  Scenario: No previous tide, on non-default branch, matching recursive changes
    Given the changes between the reference "master" and "12345" are:
      | status   | filename                   |
      | modified | app/config.yml             |
      | added    | docker/base/etc/nginx.conf |
    When a tide is started for the branch "feature/foo" and commit "12345"
    Then the task "base_images" should be "successful"

  Scenario: No previous tide, on non-default branch, non-matching changes
    Given the changes between the reference "master" and "12345" are:
      | status   | filename   |
      | added    | Dockerfile |
    When a tide is started for the branch "feature/foo" and commit "12345"
    Then the task "base_images" should be "skipped"
