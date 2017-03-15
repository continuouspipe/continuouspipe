Feature:
  In order to be able to download the code from the code repository
  As a river
  I want to be able to communicate the archive to dowmload to the builder service

  Scenario: It will send the archive URL
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        images:
            build:
                services:
                    first:
                        image: foo/bar
    """
    And a tide is started
    Then the build should be started with a BitBucket archive URL

  Scenario: It will give the correct URL without spaces in the name
    Given I have a flow with a BitBucket repository named "My example" with slug "my-example" and owned by user "foo"
    And there is the add-on installed for the BitBucket repository "my-example" owned by user "foo"
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        images:
            build:
                services:
                    first:
                        image: foo/bar
    """
    When a tide is started
    Then the build should be started with a BitBucket archive URL for the repository "foo/my-example"

  Scenario: It will send the Authorization header
    Given I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        images:
            build:
                services:
                    first:
                        image: foo/bar
    """
    And a tide is started
    Then the build should be started with an archive containing the "Authorization" header
