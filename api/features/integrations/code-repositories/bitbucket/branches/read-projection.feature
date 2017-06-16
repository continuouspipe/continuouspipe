Feature:
  In order to display the branches of a repository
  As a system
  I want to write a read projection

  Background:
    Given I am authenticated as "samuel"
    And the team "samuel" exists
    And the user "samuel" is "USER" of the team "samuel"
    And I have a flow "d7825625-f775-4ab9-b91c-b93813871bc7" with a BitBucket repository named "My example" with slug "my-example" and owned by user "sroze"
    And there is the add-on installed for the BitBucket repository "my-example" owned by user "sroze"

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
    And the following branches exist in the bitbucket repository with slug "my-example":
      | name    |
      | master  |
      | develop |
    When the commit "12345" is pushed to the branch "master"
    Then the branch "master" for the flow "d7825625-f775-4ab9-b91c-b93813871bc7" should be saved to the permanent storage of views
    And the branch "develop" for the flow "d7825625-f775-4ab9-b91c-b93813871bc7" should be saved to the permanent storage of views

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
    And the following branches exist in the bitbucket repository with slug "my-example" and are paginated in the api response:
      | name    |
      | master  |
      | develop |
    When the commit "12345" is pushed to the branch "master"
    Then the branch "master" for the flow "d7825625-f775-4ab9-b91c-b93813871bc7" should be saved to the permanent storage of views
    And the branch "develop" for the flow "d7825625-f775-4ab9-b91c-b93813871bc7" should be saved to the permanent storage of views

  Scenario: It includes the latest commit in the branch
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
    And the following branches exist in the bitbucket repository with slug "my-example":
      | name    | sha   | url                                                   |
      | master  | 12345 | https://bitbucket.org/samuel/my-example/commits/12345 |
      | develop | abcde | https://bitbucket.org/samuel/my-example/commits/abcde |
    When the commit "12345" is pushed to the branch "master"
    Then the following branches for the flow "d7825625-f775-4ab9-b91c-b93813871bc7" should be saved to the permanent storage of views:
      | name    | sha   | url                                                   |
      | master  | 12345 | https://bitbucket.org/samuel/my-example/commits/12345 |
      | develop | abcde | https://bitbucket.org/samuel/my-example/commits/abcde |
