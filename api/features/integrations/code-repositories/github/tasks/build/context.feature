Feature:
  In order to be able to download the code repository
  As a system
  I want to be able to communicate the authentication token to the builder service

  Scenario: It will send the GitHub installation token
    Given the GitHub account "sroze" have the installation "123456"
    And the token of the GitHub installation "123456" is "123456"
    When I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        images:
            build:
                services:
                    first:
                        image: foo/bar
    """
    And a tide is started
    Then the build should be started with the repository token "123456"
