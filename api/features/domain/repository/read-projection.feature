Feature:
  In order to display the branches of a repository
  As a system
  I want to write a read projection in Firebase

  Background:
    Given I am authenticated as "samuel"
    And the team "samuel" exists
    And the user "samuel" is "USER" of the team "samuel"
    And I have a flow with UUID "d7825625-f775-4ab9-b91c-b93813871bc7"
    And there is a "master" branch in the repository for the flow "d7825625-f775-4ab9-b91c-b93813871bc7"
    And there is a "develop" branch in the repository for the flow "d7825625-f775-4ab9-b91c-b93813871bc7"
    And the head commit of branch "master" is "1234"
    And there is 1 application images in the repository

  Scenario: It creates the read model for all branches in firebase
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
    Then the branch "master" for the flow "d7825625-f775-4ab9-b91c-b93813871bc7" should be saved to the permanent storage of views
    And the branch "develop" for the flow "d7825625-f775-4ab9-b91c-b93813871bc7" should be saved to the permanent storage of views
