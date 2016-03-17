Feature:

  Scenario: The environment should be deleted when the branch is deleted
    Given a tide is created for branch "foo" and commit "12345" with a deploy task
    And the tide starts
    And the deployment succeed
    When the branch "foo" with head "12345" is deleted
    Then the environment should be deleted
