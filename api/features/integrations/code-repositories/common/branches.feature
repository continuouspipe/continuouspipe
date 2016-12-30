Feature:
  In order to uses the cluster efficiently
  As a user
  I want the pull-request environments deleted when the branch is delete

  Scenario: The environment should be deleted when the branch is deleted
    Given a tide is created for branch "foo" and commit "12345" with a deploy task
    And the tide starts
    And the deployment succeed
    When the branch "foo" with head "12345" is deleted
    Then the environment should be deleted

  Scenario: It should not remove any other branch environment
    Given a tide is created for branch "foo" and commit "12345" with a deploy task
    And the tide starts
    And the deployment succeed
    When the branch "master" with head "12345" is deleted
    Then the environment should not be deleted

  Scenario: The environment should not be deleted if a tide is running for this branch
    Given a tide is created for branch "foo" and commit "12345" with a deploy task
    And the tide starts
    When the branch "foo" with head "12345" is deleted
    Then the environment deletion should be delayed

  Scenario: Pending tide
    Given there is a pending tide created for branch "foo" and commit "12345"
    When the branch "foo" with head "12345" is deleted
    Then the environment deletion should be delayed
