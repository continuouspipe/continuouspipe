Feature:
  In order to orchestrates tides for different scenarios
  As a developer
  I want to be able to run manually a tide

  Background:
    Given I am authenticated as "samuel"
    And the team "samuel" exists
    And the user "samuel" is "USER" of the team "samuel"

  Scenario: I can create a tide with a branch name and commit SHA1
    Given I have a flow with the following configuration:
    """
    tasks: [ { build: { services: [] } } ]
    """
    When I send a tide creation request for branch "foo" and commit "1234"
    Then a tide should be created

  Scenario: I can create a tide with a branch name if the branch name is resolvable
    Given I have a flow with the following configuration:
    """
    tasks: [ { build: { services: [] } } ]
    """
    And the head commit of branch "foo" is "4321"
    When I send a tide creation request for branch "foo"
    Then a tide should be created

  Scenario: An error should be send back if the branch does not exist
    Given I have a flow with the following configuration:
    """
    tasks: [ { build: { services: [] } } ]
    """
    When I send a tide creation request for branch "foo"
    Then a bad request error should be returned

  Scenario: An error should be sent back if the request does not contain the branch name
    Given I have a flow with the following configuration:
    """
    tasks: [ { build: { services: [] } } ]
    """
    When I send a tide creation request for commit "1234"
    Then a bad request error should be returned

  Scenario: As a non team-member, I shouldn't be able to create a tide
    Given I have a flow with the following configuration:
    """
    tasks: [ { build: { services: [] } } ]
    """
    Given I am authenticated as "somebody-else"
    When I send a tide creation request for branch "foo" and commit "1234"
    Then a permission error should be returned

  Scenario: A log is displayed and the tide fails when a tide is created without tasks
    Given I have a flow with the following configuration:
    """
    {}
    """
    And the commit "3b0110193e36b317207909163d0a582f6f568qwe" is pushed to the branch "feature"
    When the tide for the branch "feature" and commit "3b0110193e36b317207909163d0a582f6f568qwe" is tentatively started
    And the tide should be failed
    And a log containing "You need to configure tasks to be run for the tide." should be created
