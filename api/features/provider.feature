Feature:
  In order to deploy environment
  As a user
  I need to manage the providers

  Background:
    Given I am authenticated

  Scenario: A can list the providers
    Given I have a provider named "foo"
    When I request the list of providers
    Then I should see this provider "foo" in the list of registered providers
    And I should see the type of the providers

  Scenario: I can have a list of environments
    Given I have a provider named "foo"
    When I request the environment list of provider "foo"
    Then I should successfully receive the environment list

  Scenario: I can delete an environment
    Given I have a provider named "foo"
    And I have an environment "bar"
    When I delete the environment named "bar" of provider "foo"
    When I request the environment list of provider "foo"
    Then the environment "bar" shouldn't exists

  Scenario: Delete a provider
    Given I have a provider named "foo"
    When I delete the provider named "foo"
    Then the provider "foo" should not exists
