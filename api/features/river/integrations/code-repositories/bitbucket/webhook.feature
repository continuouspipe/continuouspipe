Feature:
  In order to take actions when an event occurs in the code repository
  As a system
  I need to be notified by a webhook

  Background:
    Given I have a flow
    And the add-on "connection:12345" is installed for the user account "geza"

  Scenario: Webhooks are secured by JSON Web Token
    When a pull-request is created with good signature
    Then processing the webhook should be accepted

  Scenario: Deny processing the request when JWT signature is not valid
    When a pull-request is created with invalid signature
    Then processing the webhook should be denied

  Scenario: Deny processing the request when JWT is missing from the request
    When a pull-request is created without JSON Web Token
    Then processing the webhook should be denied

  Scenario: Token verification cannot be bypassed by the "none" algorithm
    When a pull-request is created with good signature using the "none" algorithm
    Then processing the webhook should be denied