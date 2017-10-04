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

  Scenario: The encrypted variables are replaced with their decrypted value in the configuration
    Given the decrypted version of the value "0987654321qwertyu" for the flow "00000000-0000-0000-0000-000000000000" will be "something"
    And I have a flow with UUID "00000000-0000-0000-0000-000000000000" and the following configuration:
    """
    variables:
        - name: FOO
          encrypted_value: 0987654321qwertyu
    """
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        named:
            deploy:
                cluster: ${FOO}
                services: []
    """
    When a tide is created
    Then the configuration of the tide should contain at least:
    """
    tasks:
        named:
            deploy:
                cluster: something
    """

  Scenario: The tide fails if the decryption fails
    Given I have a flow with UUID "00000000-0000-0000-0000-000000000000" and the following configuration:
    """
    variables:
        - name: FOO
          encrypted_value: 0987654321qwertyu
    """
    And I have a "continuous-pipe.yml" file in my repository that contains:
    """
    tasks:
        named:
            deploy:
                cluster: ${FOO}
                services: []
    """
    When a tide is created
    Then the tide should be failed
    And a log containing 'Unable to decrypt the value of the variable "FOO"' should be created
