Feature:
  In order to easily create or update environments
  As a developer
  I should be able to send a environment configuration and ask Pipe to do what it can to have this environment ready

  Background:
    Given I am authenticated

  Scenario:
    When I send a valid deployment request
    Then the environment should be created or updated
