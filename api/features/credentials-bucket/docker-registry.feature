Feature:
  In order to manage the access to my Docker registries
  As a user
  I want to be able to add, update or remove docker registry credentials

  Background:
    Given I am authenticated as user "samuel"
    And the user "samuel" have access to the bucket "00000000-0000-0000-0000-000000000000"

  Scenario: I can create new Docker Registry credentials
    When I create a new docker registry with the following configuration in the bucket "00000000-0000-0000-0000-000000000000":
      | serverAddress | username | password | email                 |
      | docker.io     | foo      | bar      | samuel.roze@gmail.com |
    Then the new credentials should have been saved successfully

  Scenario: The credentials creation fails if some fields are missing
    When I create a new docker registry with the following configuration in the bucket "00000000-0000-0000-0000-000000000000":
      | serverAddress | username | password |
      | docker.io     | foo      | bar      |
    Then I should receive a bad request error

  Scenario: I can list the Docker Registry credentials of a bucket
    Given I have the following docker registry credentials in the bucket "00000000-0000-0000-0000-000000000000":
      | serverAddress | username | password | email                 |
      | docker.io     | foo      | bar      | samuel.roze@gmail.com |
    When I ask the list of the docker registry credentials in the bucket "00000000-0000-0000-0000-000000000000"
    Then I should receive a list
    And the list should contain the credential for server "docker.io"

  Scenario: I can delete a docker registry credentials by the server name
    Given I have the following docker registry credentials in the bucket "00000000-0000-0000-0000-000000000000":
      | serverAddress | username | password | email                 |
      | docker.io     | foo      | bar      | samuel.roze@gmail.com |
    When I delete the credentials of the docker registry "docker.io" from the bucket "00000000-0000-0000-0000-000000000000"
    When I ask the list of the docker registry credentials in the bucket "00000000-0000-0000-0000-000000000000"
    Then the list should not contain the credential for server "docker.io"
