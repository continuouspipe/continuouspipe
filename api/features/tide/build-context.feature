Feature:
  In order to build complex applications
  As a developer
  I want the docker-compose build context to be used

  Scenario:
    Given I have a flow with the build and deploy tasks
    And there is an application image in the repository with Dockerfile path "./sub-directory/my-Dockerfile"
    When a tide is started
    Then the build should be started with Dockerfile path "./sub-directory/my-Dockerfile" in the context

  Scenario:
    Given I have a flow with the build task
    And there is 1 application images in the repository
    When a tide is started
    Then the build should be started with the sub-directory "./0"
