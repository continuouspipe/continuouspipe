Feature:
  In order to manage the access to my Docker registries
  As a user
  I want to be able to add, update or remove docker registry credentials

  Background:
    And the user "samuel" have access to the bucket "00000000-0000-0000-0000-000000000000"

  Scenario: I can create new Docker Registry credentials
    Given I am authenticated as user "samuel"
    When I create a new docker registry with the following configuration in the bucket "00000000-0000-0000-0000-000000000000":
      | serverAddress | username | password | email                 |
      | docker.io     | foo      | bar      | samuel.roze@gmail.com |
    Then the new credentials should have been saved successfully

  Scenario: The credentials creation fails if some fields are missing
    Given I am authenticated as user "samuel"
    When I create a new docker registry with the following configuration in the bucket "00000000-0000-0000-0000-000000000000":
      | serverAddress | username | password |
      | docker.io     | foo      | bar      |
    Then I should receive a bad request error

  Scenario: Cannot create another Docker Registry with the same serverAddress
    Given I am authenticated as user "samuel"
    And I have the following docker registry credentials in the bucket "00000000-0000-0000-0000-000000000000":
      | serverAddress | username | password | email                 |
      | docker.io     | foo      | bar      | samuel.roze@gmail.com |
    When I create a new docker registry with the following configuration in the bucket "00000000-0000-0000-0000-000000000000":
      | serverAddress | username | password | email                 |
      | docker.io     | foo2     | bar2     | samuel.roze2@gmail.com|
    Then the new credentials should not have been saved successfully

  @smoke
  Scenario: I can list the Docker Registry credentials of a bucket
    Given I am authenticated as user "samuel"
    And I have the following docker registry credentials in the bucket "00000000-0000-0000-0000-000000000000":
      | serverAddress | username | password | email                 |
      | docker.io     | foo      | bar      | samuel.roze@gmail.com |
    When I ask the list of the docker registry credentials in the bucket "00000000-0000-0000-0000-000000000000"
    Then I should receive a list
    And the list should contain the credential for server "docker.io"

  Scenario: I can delete a docker registry credentials by the server name
    Given I am authenticated as user "samuel"
    And I have the following docker registry credentials in the bucket "00000000-0000-0000-0000-000000000000":
      | serverAddress | username | password | email                 |
      | docker.io     | foo      | bar      | samuel.roze@gmail.com |
    When I delete the credentials of the docker registry "docker.io" from the bucket "00000000-0000-0000-0000-000000000000"
    When I ask the list of the docker registry credentials in the bucket "00000000-0000-0000-0000-000000000000"
    Then the list should not contain the credential for server "docker.io"

  Scenario: As a system user, I can create Docker Registries
    Given there is the system api key "1234"
    When I create a new docker registry with the following configuration in the bucket "00000000-0000-0000-0000-000000000000" with the API key "1234":
      | serverAddress | username | password | email                 |
      | docker.io     | foo      | bar      | samuel.roze@gmail.com |
    Then the new credentials should have been saved successfully

  Scenario: I can create Docker registries with the full path
    Given I am authenticated as user "samuel"
    When I create a new docker registry with the following configuration in the bucket "00000000-0000-0000-0000-000000000000":
      | full_address    | username | password | email                 |
      | quay.io/foo/bar | foo      | bar      | samuel.roze@gmail.com |
    Then the new credentials should have been saved successfully

  @smoke
  Scenario: I can create Docker registries with attributes
    Given I am authenticated as user "samuel"
    When I create a new docker registry with the following configuration in the bucket "00000000-0000-0000-0000-000000000000":
      | full_address    | username | password | email                 | attributes                        |
      | quay.io/foo/bar | foo      | bar      | samuel.roze@gmail.com | {"created-by": "continuous-pipe"} |
    And I ask the list of the docker registry credentials in the bucket "00000000-0000-0000-0000-000000000000"
    Then the list should contain the credential for server "quay.io"
    And the registry "quay.io/foo/bar" should have the attribute "created-by" valued "continuous-pipe"
