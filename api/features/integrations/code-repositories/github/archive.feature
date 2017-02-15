Feature:
  In order to successfully download the code from GitHub
  As a builder client
  I want the builder to use an archive that will always have up-to-date credentials

  Scenario: I can proxy authenticated requests to GitHub
    Given the GitHub account "foo" have the installation "0000"
    And the token of the GitHub installation "0000" is "1234"
    And the URL "https://api.github.com/repos/foo/bar/tarball/sha1" will return "OK" with the header "Authorization" valued "token 1234"
    And I have a flow "00000000-0000-0000-0000-000000000000" with a GitHub repository "bar" owned by "foo"
    When I request the archive of the repository for the flow "00000000-0000-0000-0000-000000000000" and reference "sha1"
    Then I should receive the archive value "OK"

  Scenario: It will start the GitHub build using the archive address with the according secret
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
    Then the build should be started with an archive
