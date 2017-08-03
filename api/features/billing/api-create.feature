Feature:
  In order to separate billing profiles for different projects
  As a user
  I want to be able to create billing profiles

  Background:
    Given there is a user "dave"

  Scenario: I can create a billing profile
    Given I am authenticated as user "dave"
    When I create a billing profile "alternative"
    Then the billing profile "alternative" for "dave" should have been created
