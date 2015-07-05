Feature: In order to be able to test docker based projects
  As a developer
  I should be able to have my application image built for pull-requests

  Scenario: A new image tag should be created when I open a pull request
    When I create a new pull request on my repository
    Then a new tag of the built image should be created

  Scenario: The tag should be updated when I add commits
    When I push a new commit on an existing pull-request
    Then the related tag should be updated with the new built image
