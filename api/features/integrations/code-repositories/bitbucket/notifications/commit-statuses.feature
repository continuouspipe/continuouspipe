Feature:
  In order to be notified of the tides' statuses
  As a developer
  I want to have a commit status on my BitBucket commits

  Background:
    Given I have a flow with a BitBucket repository "example" owned by user "foo"
    And there is the add-on installed for the BitBucket repository "example" owned by user "foo"
    And there is a "continuous-pipe.yml" file in my BitBucket repository that contains:
    """
    tasks:
        images:
            build: {services: []}
    """
    And the BitBucket build status request will succeed

  Scenario: The first status is stopped when I just push something
    When I push the commit "12345" to the branch "master" of the BitBucket repository "example" owned by user "foo"
    Then the BitBucket build status of the commit "12345" should be stopped
    And the tide should be created

  Scenario: The status is successful when tide is successful
    Given a tide is created for branch "master" and commit "12345" with a deploy task
    When the tide is successful
    Then the BitBucket build status of the commit "12345" should be successful
