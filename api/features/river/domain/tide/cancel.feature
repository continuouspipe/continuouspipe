Feature:
  In order to speed-up my flows in case of problem
  As a user
  I want to be able to cancel a running tide

  Background:
    Given there is 1 application images in the repository
    And I have a flow
    And I am authenticated as "samuel"
    And the user "samuel" is "USER" of the team "samuel"

  @smoke
  Scenario: The following tasks are not ran
    Given a tide is started with a build and deploy task
    When I cancel the tide
    And all the image builds are successful
    Then the deploy task should not be started
    And the tide should be cancelled
    Then a log containing 'Tide manually cancelled by samuel' should be created

  Scenario: Cancel a pending tide
    Given a tide is created with just a build task
    When I cancel the tide
    Then the tide should be cancelled
    Then a log containing 'Tide manually cancelled by samuel' should be created

  Scenario: It cancels the running task
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        images:
            build: ~

        deployment:
            deploy:
                cluster: foo
                services:
                    mysql:
                        specification:
                            source:
                                image: mysql

        fixtures:
            run:
                cluster: foo
                image: busybox
                commands:
                    - foo
    """
    And a tide is started
    And all the image builds are successful
    When I cancel the tide
    Then the task named "images" should be successful
    And the task named "deployment" should be cancelled
    And the task named "fixtures" should be pending
