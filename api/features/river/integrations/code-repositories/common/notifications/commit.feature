Feature:
  In order to have a feedback about my feature
  As a developer
  I want to see the tide status on the code repository interface

  Background:
    Given there is 1 application images in the repository

  Scenario: It send a notification when a tide is created
    Given I have a flow with the following configuration:
    """
    tasks:
        - build: ~
    """
    When a tide is started
    Then a commit status should have been sent

  Scenario: It send a notification even when unable to read the YAML configuration
    Given I have a flow with the following configuration:
    """
    tasks:
        - build: ~

    pipelines:
        something_wrong_matey: ~
    """
    When a tide is started
    Then a commit status should have been sent
