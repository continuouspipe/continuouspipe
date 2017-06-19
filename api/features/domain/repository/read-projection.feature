Feature:
  In order to display the branches of a repository
  As a system
  I want to write a read projection

  Background:
    Given I am authenticated as "samuel"
    And the team "samuel" exists
    And the user "samuel" is "USER" of the team "samuel"
    And I have a flow with UUID "d7825625-f775-4ab9-b91c-b93813871bc7"
    And there is a "master" branch in the repository for the flow "d7825625-f775-4ab9-b91c-b93813871bc7"
    And there is a "develop" branch in the repository for the flow "d7825625-f775-4ab9-b91c-b93813871bc7"

  Scenario: It creates the read model for all branches
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
    When the commit "12345" is pushed to the branch "master"
    Then the following branches for the flow "d7825625-f775-4ab9-b91c-b93813871bc7" should be saved to the permanent storage of views:
      | name    |
      | master  |
      | develop |

  Scenario: It stores the most recent tides for a branch
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
    And the "master" branch in the repository for the flow "d7825625-f775-4ab9-b91c-b93813871bc7" has the following tides:
      | tide                                 |
      | e635cd99-3872-4be5-8f26-9ab46c7faf36 |
      | c151b296-33d4-4e2d-8e81-de32fd0d5e30 |
    And the "develop" branch in the repository for the flow "d7825625-f775-4ab9-b91c-b93813871bc7" has the following tides:
      | tide                                 |
      | 4e09cc05-8545-4622-a35a-b0d9a62b9fde |
    When the commit "12345" is pushed to the branch "master"
    Then the "master" branch for the flow "d7825625-f775-4ab9-b91c-b93813871bc7" is stored with the following tides:
      | tide                                 |
      | e635cd99-3872-4be5-8f26-9ab46c7faf36 |
      | c151b296-33d4-4e2d-8e81-de32fd0d5e30 |
    And the "develop" branch for the flow "d7825625-f775-4ab9-b91c-b93813871bc7" is stored with the following tides:
      | tide                                 |
      | 4e09cc05-8545-4622-a35a-b0d9a62b9fde |

  Scenario: It updates the branch views when a branch is deleted
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
    When the branch "master" is deleted for the repository for the flow "d7825625-f775-4ab9-b91c-b93813871bc7"
    Then the branch "develop" for the flow "d7825625-f775-4ab9-b91c-b93813871bc7" should be saved to the permanent storage of views

  Scenario: It updates when a new tide is created
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
    And the "master" branch in the repository for the flow "d7825625-f775-4ab9-b91c-b93813871bc7" has the following tides:
      | tide                                 |
      | e635cd99-3872-4be5-8f26-9ab46c7faf36 |
      | c151b296-33d4-4e2d-8e81-de32fd0d5e30 |
    And the "develop" branch in the repository for the flow "d7825625-f775-4ab9-b91c-b93813871bc7" has the following tides:
      | tide                                 |
      | 4e09cc05-8545-4622-a35a-b0d9a62b9fde |
    When the commit "12345" is pushed to the branch "master"
    And I create a tide "fc256d7a-2a8d-46e9-836a-e9ddec711f84" for the flow "d7825625-f775-4ab9-b91c-b93813871bc7" for branch "develop" and commit "12345"
    Then the "master" branch for the flow "d7825625-f775-4ab9-b91c-b93813871bc7" is stored with the following tides:
      | tide                                 |
      | e635cd99-3872-4be5-8f26-9ab46c7faf36 |
      | c151b296-33d4-4e2d-8e81-de32fd0d5e30 |
    And the "develop" branch for the flow "d7825625-f775-4ab9-b91c-b93813871bc7" is stored with the following tides:
      | tide                                 |
      | fc256d7a-2a8d-46e9-836a-e9ddec711f84 |
      | 4e09cc05-8545-4622-a35a-b0d9a62b9fde |

  Scenario: It creates the read model for pull requests when they are opened
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
    And there is a "feature/new-feature" branch in the repository for the flow "d7825625-f775-4ab9-b91c-b93813871bc7"
    When I open a pull request "4" titled "Please review my new feature" for commit "4567" the branch "feature/new-feature" for the flow "d7825625-f775-4ab9-b91c-b93813871bc7"
    Then the pull request "4" titled "Please review my new feature" for branch "feature/new-feature" of flow "d7825625-f775-4ab9-b91c-b93813871bc7" should be saved to the permanent storage of views

  Scenario: It removes the read model for pull requests when they are closed
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
    And there is a "successful" tide for the branch "feature/new-feature"
    And I open a pull request "4" titled "Please review my new feature" for commit "4567" the branch "feature/new-feature" for the flow "d7825625-f775-4ab9-b91c-b93813871bc7"
    When I close the pull request "4" titled "Please review my new feature" for commit "4567" of the branch "feature/new-feature" for the flow "d7825625-f775-4ab9-b91c-b93813871bc7"
    Then the pull request "4" titled "Please review my new feature" for branch "feature/new-feature" of flow "d7825625-f775-4ab9-b91c-b93813871bc7" should not be in the permanent storage of views

  Scenario: It removes the read model for pull requests when the branch is deleted
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
    And there is a "feature/new-feature" branch in the repository for the flow "d7825625-f775-4ab9-b91c-b93813871bc7"
    And I open a pull request "4" titled "Please review my new feature" for commit "4567" the branch "feature/new-feature" for the flow "d7825625-f775-4ab9-b91c-b93813871bc7"
    When the branch "feature/new-feature" is deleted for the repository for the flow "d7825625-f775-4ab9-b91c-b93813871bc7"
    Then the pull request "4" titled "Please review my new feature" for branch "feature/new-feature" of flow "d7825625-f775-4ab9-b91c-b93813871bc7" should not be in the permanent storage of views

  Scenario: It creates the read model for all branches
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
    When I refresh the branches and pull requests for the flow "d7825625-f775-4ab9-b91c-b93813871bc7"
    Then the branch "master" for the flow "d7825625-f775-4ab9-b91c-b93813871bc7" should be saved to the permanent storage of views
    And the branch "develop" for the flow "d7825625-f775-4ab9-b91c-b93813871bc7" should be saved to the permanent storage of views