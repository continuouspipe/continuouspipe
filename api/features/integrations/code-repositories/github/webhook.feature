Feature:
  In order to take actions when an event occurs in the code repository
  As a system
  I need to be notified by a webhook

  Background:
    Given I have a flow

  Scenario: Webhooks are secured by secret token
    When a pull-request is created with invalid signature
    Then processing the webhook should be denied

  Scenario: Webhooks are secured by secret token
    When a pull-request is created with good signature
    Then processing the webhook should be successfully completed

  Scenario: Integration webhooks are secured by secret token
    When a push webhook is received with invalid signature
    Then processing the webhook should be denied

  Scenario: Integration webhooks are secured by secret token
    When a push webhook is received with good signature
    Then processing the webhook should be accepted