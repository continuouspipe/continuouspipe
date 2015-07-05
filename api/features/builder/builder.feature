Feature: In order to be able to manage docker applications
  As a developer
  Builder must build Docker images from my project source

  Scenario: I have a simple one-container application
    Given I have a repository containing a Dockerfile
    When I push my code to this repository
    Then the application image should be automatically built
