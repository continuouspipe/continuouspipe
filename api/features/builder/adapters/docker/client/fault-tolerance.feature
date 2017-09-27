Feature:
  In order to reduce the tide errors
  The system should be fault tolerant

  Scenario: Retry push if it fails
    Given the push will fail because of a daemon error the first time
    And the push will be successful the second time
    When a built image is pushed
    Then the push should be successful
