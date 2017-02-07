Feature:
  In order to protect my application's configuration variables
  As a user
  I want to be able to store encrypted variables

  Background:
    Given the team "my-team" exists
    And there is a user "samuel"
    And the user "samuel" is "ADMIN" of the team "my-team"
    And I have a flow with UUID "00000000-0000-0000-0000-000000000000" in the team "my-team"

  Scenario: I can encrypt a value using an API
    Given I am authenticated as "samuel"
    And the encrypted version of the value "something" for the flow "00000000-0000-0000-0000-000000000000" will be "0987654321qwertyu"
    When I request the encrypted value of "something" for the flow "00000000-0000-0000-0000-000000000000"
    Then I should receive the encrypted value "0987654321qwertyu"

  Scenario: A user cannot encrypt a value
    Given there is a user "foo"
    And the user "foo" is "USER" of the team "my-team"
    Given I am authenticated as "foo"
    When I request the encrypted value of "something" for the flow "00000000-0000-0000-0000-000000000000"
    Then the encryption should be forbidden
