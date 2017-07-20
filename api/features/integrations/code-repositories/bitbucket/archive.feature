Feature:
  In order to be able to download the code from the code repository
  As a river
  I want to be able to communicate the archive to download to the builder service. River will expose a path
  that will proxy the downloading request.

  Scenario: I can proxy authenticated requests to BitBucket
    Given there is the add-on installed for the BitBucket repository "my-example" owned by user "foo"
    And I have a flow "00000000-0000-0000-0000-000000000000" with a BitBucket repository named "My example" with slug "my-example" and owned by user "foo"
    And the BitBucket URL "https://bitbucket.org/foo/my-example/get/sha1.tar.gz" will return "OK" with the header "Authorization"
    When I request the archive of the repository for the flow "00000000-0000-0000-0000-000000000000" and reference "sha1"
    Then I should receive the archive value "OK"
