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
    When I ask the details of the bucket "00000000-0000-0000-0000-000000000000"
    Then I should be told that I don't have the authorization for this bucket

  @smoke
  Scenario: I can have access to my teams' buckets
    Given the user "samuel" is in the team "foo"
    And the bucket of the team "foo" is the "00000000-0000-0000-0000-000000000000"
    When I ask the list of the docker registry credentials in the bucket "00000000-0000-0000-0000-000000000000"
    Then I should see the list of the docker registry credentials

  Scenario: The secrets are obfuscated for normal users
    Given the user "samuel" is in the team "foo"
    And the bucket of the team "foo" is the "00000000-0000-0000-0000-000000000000"
    And I have the following GitHub tokens in the bucket "00000000-0000-0000-0000-000000000000":
      | identifier | accessToken                              |
      | sroze      | e72e16c7e42f292c6912e7710c838347ae178b4a |
    And I have the following docker registry credentials in the bucket "00000000-0000-0000-0000-000000000000":
      | serverAddress | username | password | email                 |
      | docker.io     | foo      | bar      | samuel.roze@gmail.com |
    And I have the following clusters in the bucket "00000000-0000-0000-0000-000000000000":
      | identifier | type       | address         | username | password | client_certificate | google_cloud_service_account | version |
      | my-kube    | kubernetes | https://1.2.3.4 | samuel   | roze     | base64             | base64                       | v1.2    |
    When I ask the list of the clusters in the bucket "00000000-0000-0000-0000-000000000000"
    Then the "password" should be obfuscated in the list items
    Then the "client_certificate" should be obfuscated in the list items
    Then the "google_cloud_service_account" should be obfuscated in the list items
    When I ask the list of the docker registry credentials in the bucket "00000000-0000-0000-0000-000000000000"
    Then the "password" should be obfuscated in the list items
    When I ask the list of the GitHub tokens in the bucket "00000000-0000-0000-0000-000000000000"
    Then the "accessToken" should be obfuscated in the list items

  Scenario: The secrets are not obfuscated for system users
    Given there is the system api key "1234567890"
    And the user "samuel" is in the team "foo"
    And the bucket of the team "foo" is the "00000000-0000-0000-0000-000000000000"
    And there is a bucket "00000000-0000-0000-0000-000000000000"
    And I have the following GitHub tokens in the bucket "00000000-0000-0000-0000-000000000000":
      | identifier | accessToken                              |
      | sroze      | e72e16c7e42f292c6912e7710c838347ae178b4a |
    And I have the following docker registry credentials in the bucket "00000000-0000-0000-0000-000000000000":
      | serverAddress | username | password | email                 |
      | docker.io     | foo      | bar      | samuel.roze@gmail.com |
    And I have the following clusters in the bucket "00000000-0000-0000-0000-000000000000":
      | identifier | type       | address         | username | password | client_certificate | google_cloud_service_account | version |
      | my-kube    | kubernetes | https://1.2.3.4 | samuel   | roze     | base64             | base64                       | v1.2    |
    And I am not authenticated
    When I ask the list of the clusters in the bucket "00000000-0000-0000-0000-000000000000" with the API key "1234567890"
    Then the "password" should not be obfuscated in the list items
    Then the "client_certificate" should not be obfuscated in the list items
    Then the "google_cloud_service_account" should not be obfuscated in the list items
    When I ask the list of the docker registry credentials in the bucket "00000000-0000-0000-0000-000000000000" with the API key "1234567890"
    Then the "password" should not be obfuscated in the list items
    When I ask the list of the GitHub tokens in the bucket "00000000-0000-0000-0000-000000000000" with the API key "1234567890"
    Then the "accessToken" should not be obfuscated in the list items
