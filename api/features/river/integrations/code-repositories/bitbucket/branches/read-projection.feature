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
    And the following branches exist in the bitbucket repository with slug "my-example" for user "sroze":
      | name    |
      | master  |
      | develop |
    When the commit "12345" is pushed to the branch "master"
    Then the following branches for the flow "d7825625-f775-4ab9-b91c-b93813871bc7" should be saved to the permanent storage of views:
      | name    | url                                                   | pinned |
      | master  | https://bitbucket.org/sroze/my-example/branch/master  | false  |
      | develop | https://bitbucket.org/sroze/my-example/branch/develop | false  |

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
    And the following branches exist in the bitbucket repository with slug "my-example" for user "sroze":
      | name    | sha   | commit-url                                           |
      | master  | 12345 | https://bitbucket.org/sroze/my-example/commits/12345 |
      | develop | abcde | https://bitbucket.org/sroze/my-example/commits/abcde |
    When the commit "12345" is pushed to the branch "master"
    Then the following branches for the flow "d7825625-f775-4ab9-b91c-b93813871bc7" should be saved to the permanent storage of views:
      | name    | sha   | commit-url                                           | url                                                   | pinned |
      | master  | 12345 | https://bitbucket.org/sroze/my-example/commits/12345 | https://bitbucket.org/sroze/my-example/branch/master  | false  |
      | develop | abcde | https://bitbucket.org/sroze/my-example/commits/abcde | https://bitbucket.org/sroze/my-example/branch/develop | false  |

  Scenario: It also updates the pull requests
    Given there is a repository identifier "987987"
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        images:
            build: ~

        deployment:
            deploy:
                cluster: foo
                services: []

    """
    And the following branches exist in the bitbucket repository with slug "my-example" for user "sroze":
      | name    |
      | master  |
      | develop |
    And there is a Bitbucket pull-request #34 titled "A Pull Request" for branch "feature/new-feature"
    When the commit "12345" is pushed to the branch "master"
    Then the branch "master" for the flow "d7825625-f775-4ab9-b91c-b93813871bc7" should be saved to the permanent storage of views
    And the branch "develop" for the flow "d7825625-f775-4ab9-b91c-b93813871bc7" should be saved to the permanent storage of views
    And the pull request "34" titled "A Pull Request" for branch "feature/new-feature" of flow "d7825625-f775-4ab9-b91c-b93813871bc7" should be saved to the permanent storage of views with url "https://bitbucket.org/sroze/php-example/pull-requests/34"

  Scenario: It also adds the commit datetime
    Given there is a repository identifier "987987"
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        images:
            build: ~

        deployment:
            deploy:
                cluster: foo
                services: []

    """
    And the following branches exist in the bitbucket repository with slug "my-example" for user "sroze":
      | name    | sha   | commit-url                                           | datetime                  |
      | master  | 12345 | https://bitbucket.org/sroze/my-example/commits/12345 | 2016-12-27T13:04:07+00:00 |
    When the commit "12345" is pushed to the branch "master"
    Then the branch "master" for the flow "d7825625-f775-4ab9-b91c-b93813871bc7" should be saved to the permanent storage of views with a not null datetime
