Feature:
  In order to successfully download the code from GitHub
  As a builder client
  I want the builder to use an archive that will always have up-to-date credentials

  Scenario: I can proxy authenticated requests to GitHub
    Given the GitHub account "foo" have the installation "0000"
    And the token of the GitHub installation "0000" is "1234"
    And the URL "https://api.github.com/repos/foo/bar/tarball/sha1" will return the content of the fixtures file "empty.tgz" with the header "Authorization" valued "token 1234"
    And I have a flow "00000000-0000-0000-0000-000000000000" with a GitHub repository "bar" owned by "foo"
    When I request the archive of the repository for the flow "00000000-0000-0000-0000-000000000000" and reference "sha1"
    Then I should receive a targz archive

  Scenario: It will start the GitHub build using the archive address
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
    And the build should be started with an archive containing the "Authorization" header

  Scenario: It will refuse the access if I don't have the JWT token header
    Given the GitHub account "foo" have the installation "0000"
    And the token of the GitHub installation "0000" is "1234"
    And the URL "https://api.github.com/repos/foo/bar/tarball/sha1" will return the content of the fixtures file "empty.tgz" with the header "Authorization" valued "token 1234"
    And I have a flow "00000000-0000-0000-0000-000000000000" with a GitHub repository "bar" owned by "foo"
    When I request the archive of the repository for the flow "00000000-0000-0000-0000-000000000000" and reference "sha1" without credentials
    Then I should be told that I am not authenticated

  Scenario: It will refuse the access if I don't have a token with valid permissions
    Given there is a user "sam"
    And the GitHub account "foo" have the installation "0000"
    And the token of the GitHub installation "0000" is "1234"
    And the URL "https://api.github.com/repos/foo/bar/tarball/sha1" will return the content of the fixtures file "empty.tgz" with the header "Authorization" valued "token 1234"
    And I have a flow "00000000-0000-0000-0000-000000000000" with a GitHub repository "bar" owned by "foo"
    When I request the archive of the repository for the flow "00000000-0000-0000-0000-000000000000" and reference "sha1" with the token for user "sam"
    Then I should be told that I don't have the permissions
