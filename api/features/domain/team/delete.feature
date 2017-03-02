Feature:
  In order to delete teams that no longer needed
  As a user
  I want to be able to remove a team

  Background:
    Given I am authenticated

  Scenario: Able to delete a team
    Given the team "continuous-pipe" exists
    When I delete the team named "continuous-pipe"
    Then the team is successfully deleted
    And I should not see the team "continuous-pipe"

  Scenario: Unable to delete a team when it has flows associated with it
    Given the team "continuous-pipe" exists
    And I have a flow in the team "continuous-pipe"
    When I delete the team named "continuous-pipe"
    Then the team deletion should fail
    And I should be notified that
    """
    The team cannot be deleted, because it has flows associated with it. Delete these flows first.
    """
