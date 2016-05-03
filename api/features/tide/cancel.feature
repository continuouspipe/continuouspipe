Feature:
  In order to speed-up my flows in case of problem
  As a user
  I want to be able to cancel a running tide

  Background:
    Given there is 1 application images in the repository
    And I have a flow
    And I am authenticated

  @smoke
  Scenario: The following tasks are not ran
    Given a tide is started with a build and deploy task
    When I cancel the tide
    And all the image builds are successful
    Then the deploy task should not be started
    And the tide should be failed

  Scenario: Cancel a pending tide
    Given a tide is created with just a build task
    When I cancel the tide
    Then the tide should be failed
