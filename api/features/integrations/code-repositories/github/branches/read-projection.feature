Feature:
  In order to display the branches of a repository
  As a system
  I want to write a read projection

  Background:
    Given I am authenticated as "samuel"
    And the team "samuel" exists
    And the user "samuel" is "USER" of the team "samuel"
    And the GitHub account "sroze" have the installation "0000"
    And the token of the GitHub installation "0000" is "1234"
    And the GitHub repository "bar" exists
    And I have a flow with UUID "d7825625-f775-4ab9-b91c-b93813871bc7"

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
    And the following branches exists in the github repository:
      | name    |
      | master  |
      | develop |
    When the commit "12345" is pushed to the branch "master"
    Then the branch "master" for the flow "d7825625-f775-4ab9-b91c-b93813871bc7" should be saved to the permanent storage of views
    And the branch "develop" for the flow "d7825625-f775-4ab9-b91c-b93813871bc7" should be saved to the permanent storage of views

  Scenario: It creates the read model for all branches when they are paginated
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
    And the following branches exists in the github repository and are paginated in the api response:
      | name    |
      | master  |
      | develop |
    When the commit "12345" is pushed to the branch "master"
    Then the branch "master" for the flow "d7825625-f775-4ab9-b91c-b93813871bc7" should be saved to the permanent storage of views
    And the branch "develop" for the flow "d7825625-f775-4ab9-b91c-b93813871bc7" should be saved to the permanent storage of views

  Scenario: It also updates the pull requests
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
    And the following branches exists in the github repository:
      | name    |
      | master  |
      | develop |
    And there is a GitHub pull-request #34 titled "A Pull Request" for branch "feature/new-feature"
    When the commit "12345" is pushed to the branch "master"
    Then the branch "master" for the flow "d7825625-f775-4ab9-b91c-b93813871bc7" should be saved to the permanent storage of views
    And the branch "develop" for the flow "d7825625-f775-4ab9-b91c-b93813871bc7" should be saved to the permanent storage of views
    And the pull request "34" titled "A Pull Request" for branch "feature/new-feature" of flow "d7825625-f775-4ab9-b91c-b93813871bc7" should be saved to the permanent storage of views

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
    And the following branches exists in the github repository:
      | name    | sha   | commit-url                                                          |
      | master  | 12345 | https://api.github.com/repos/sroze/docker-php-example/commits/12345 |
      | develop | abcde | https://api.github.com/repos/sroze/docker-php-example/commits/abcde |
    When the commit "12345" is pushed to the branch "master"
    Then the following branches for the flow "d7825625-f775-4ab9-b91c-b93813871bc7" should be saved to the permanent storage of views:
      | name    | sha   | commit-url                                               | url                                                        |
      | master  | 12345 | https://github.com/sroze/docker-php-example/commit/12345 | https://github.com/sroze/docker-php-example/branch/master  |
      | develop | abcde | https://github.com/sroze/docker-php-example/commit/abcde | https://github.com/sroze/docker-php-example/branch/develop |
