Feature:
  In order to prevent credentials leaks
  As a user
  I have to have access only to the credentials I am granted

  Background:
    Given I am authenticated as user "samuel"
    And there is a team "foo"
    And there is a bucket "00000000-0000-0000-0000-000000000000"

  Scenario: If I am not part of the team that owe the bucket I'm forbidden to access to it
    Given the bucket of the team "foo" is the "00000000-0000-0000-0000-000000000000"
    When I ask the list of the docker registry credentials in the bucket "00000000-0000-0000-0000-000000000000"
    Then I should be told that I don't have the authorization for this bucket

  Scenario: I can have access to my teams' buckets
    Given the user "samuel" is in the team "foo"
    And the bucket of the team "foo" is the "00000000-0000-0000-0000-000000000000"
    When I ask the list of the docker registry credentials in the bucket "00000000-0000-0000-0000-000000000000"
    Then I should see the list of the docker registry credentials
