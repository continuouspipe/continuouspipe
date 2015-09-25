Feature:
  In order to build complex applications
  As a developer
  I want the docker-compose build context to be used

  Scenario:
    Given there is an application image in the repository with Dockerfile path "./sub-directory/my-Dockerfile"
    When a tide is started with a build and deploy task
    Then the build should be started with Dockerfile path "./sub-directory/my-Dockerfile" in the context

  Scenario:
    Given there is 1 application images in the repository
    When a tide is started with a build task
    Then the build should be started with the sub-directory "./0"

  Scenario:
    Given there is 1 application images in the repository
    When a tide is started with a build task that have the following environment variables:
      | name | value |
      | FOO  | BAR   |
    Then the build should be started with the following environment variables:
      | name | value |
      | FOO  | BAR   |
