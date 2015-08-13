Feature:
  In order to deploy environment
  As a user
  I need to manage the providers

  Background:
    Given I am authenticated

  Scenario: A can list the providers
    Given I have a fake provider named "foo"
    Then I should see this fake provider "foo" in the list of registered providers
