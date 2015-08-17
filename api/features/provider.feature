Feature:
  In order to deploy environment
  As a user
  I need to manage the providers

  Background:
    Given I am authenticated

  Scenario: A can list the providers
    Given I have a provider named "foo"
    Then I should see this provider "foo" in the list of registered providers

  Scenario: I can test if a provider is valid
    Given I have a provider named "foo"
    When I test the provider "foo"
    Then I should see that the provider is valid
