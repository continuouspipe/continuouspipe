Feature:
  In order to manage the access to my Docker registries
  As a user
  I want to be able to add, update or remove docker registry credentials

  Background:
    Given I am authenticated as user "samuel"

  Scenario: I can create new Docker Registry credentials
    When I create a new docker registry with the following configuration:
      | serverAddress | username | password | email                 |
      | docker.io     | foo      | bar      | samuel.roze@gmail.com |
    Then the new credentials should have been saved successfully

  Scenario: The credentials creation fails if some fields are missing
    When I create a new docker registry with the following configuration:
      | serverAddress | username | password |
      | docker.io     | foo      | bar      |
    Then I should receive a bad request error

  @wip
  Scenario: I can list my Docker Registry credentials
    Given I have the following docker registry credentials:
      | serverAddress | username | password | email                 |
      | docker.io     | foo      | bar      | samuel.roze@gmail.com |
    When I ask the list of my docker registry credentials
    Then I should receive a list
    And the list should contain the credential for server "docker.io"
