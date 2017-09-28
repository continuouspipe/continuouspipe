Feature: Improve performance of flow management by caching
  In order to manage flows when caching is turned on
  As a developer
  I need to be able to see the changes applied

  Background:
    Given the authenticator cache is on
    And the team "Coders" exists
    And I am authenticated as "Samuel"
    And the user "Samuel" is "USER" of the team "Coders"
    And I have a flow in the team "Coders"

  Scenario: Caching permission check result for user A, does not affect second permission check for user B
    Given I send an update request with a configuration
    And the flow is not saved because of an authorization exception
    When I am authenticated as "Geza"
    And the user "Geza" is "ADMIN" of the team "Coders"
    And I send an update request with a configuration
    Then the flow is successfully saved
