Feature:
  In order to control the display of branches
  As an admin user
  I want to be able to ping branches

  Background:
    Given I am authenticated as "samuel"
    And the team "samuel" exists
    And the user "samuel" is "ADMIN" of the team "samuel"
    And I have a flow with UUID "d7825625-f775-4ab9-b91c-b93813871bc7"
    And there is a "important-feature" branch in the repository for the flow "d7825625-f775-4ab9-b91c-b93813871bc7"

  Scenario: It stores that a branch is pinned
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        images:
            build: ~

        deployment:
            deploy:
                cluster: foo
                services: []

    """
    When I pin the branch "important-feature" for the flow "d7825625-f775-4ab9-b91c-b93813871bc7"
    And the commit "12345" is pushed to the branch "important-feature"
    Then the branch "important-feature" for the flow "d7825625-f775-4ab9-b91c-b93813871bc7" should be saved to the permanent storage of views as a pinned branch

  Scenario: It updates branch view when a branch is pinned
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        images:
            build: ~

        deployment:
            deploy:
                cluster: foo
                services: []

    """
    When the commit "12345" is pushed to the branch "important-feature"
    And I pin the branch "important-feature" for the flow "d7825625-f775-4ab9-b91c-b93813871bc7"
    Then the branch "important-feature" for the flow "d7825625-f775-4ab9-b91c-b93813871bc7" should be saved to the permanent storage of views as a pinned branch

  Scenario: It updates branch view when a branch is pinned
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        images:
            build: ~

        deployment:
            deploy:
                cluster: foo
                services: []

    """
    When the commit "12345" is pushed to the branch "important-feature"
    And I pin the branch "important-feature" for the flow "d7825625-f775-4ab9-b91c-b93813871bc7"
    And I unpin the branch "important-feature" for the flow "d7825625-f775-4ab9-b91c-b93813871bc7"
    Then the branch "important-feature" for the flow "d7825625-f775-4ab9-b91c-b93813871bc7" should be saved to the permanent storage of views as an unpinned branch
