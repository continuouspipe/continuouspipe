Feature:
  In order to be able to use CP for a team of developers
  As a user
  I need to be able to manage a team of users

  Background:
    Given I am authenticated as user "samuel"

  @smoke
  Scenario: I can create a team
    When I create a team "continuous-pipe"
    And I request the list of teams
    Then I should see the team "continuous-pipe" in the team list
    And the user "samuel" should be administrator of the team "continuous-pipe"

  Scenario: I can add a user to a team if I am administrator
    Given there is a team "foo"
    And the user "samuel" is administrator of the team "foo"
    And there is a user "bar"
    When I add the user "bar" in the team "foo"
    Then the user should be added to the team
    And I can see the user "bar" in the team "foo"

  Scenario: I can't add a user to a team if I am not administrator of this team
    Given there is a team "foo"
    And there is a user "bar"
    When I add the user "bar" in the team "foo"
    Then I should be told that I don't have the authorization

  Scenario: I can add a user with a set of given permissions
    Given there is a team "foo"
    And the user "samuel" is administrator of the team "foo"
    And there is a user "bar"
    When I add the user "bar" in the team "foo" with the "ADMIN" permissions
    Then the user should be added to the team
    And the user "bar" should be administrator of the team "foo"

  @smoke
  Scenario: As an administrator, I can remove a user from the team
    Given there is a team "foo"
    And the user "samuel" is administrator of the team "foo"
    And there is a user "bar"
    And the user "bar" is in the team "foo"
    When I remove the user "bar" in the team "foo"
    Then the user should be deleted from the team
    Then the user "bar" shouldn't be in the team "foo"

  Scenario: Each team should have a credentials bucket
    When I create a team "newly-created"
    Then the team should be successfully created
    And the team "newly-created" should have a credentials bucket

  Scenario: I can't create a team with an invalid slug
    When I create a team "continuous pipe"
    Then the team should not be created
    And I should see that the team have an invalid stug

  Scenario: I can't create a team that already exists
    Given there is a team "continuous-pipe"
    When I create a team "continuous-pipe"
    Then the team should not be created
    And I should see that the team already exists

  @smoke
  Scenario: I can give a name to a team
    When I create a team "continuous-pipe" named "Continuous Pipe"
    Then the team should be successfully created
    And I request the list of teams
    And I should see that the team "continuous-pipe" is named "Continuous Pipe"

  Scenario: Non-members can't access the team details
    Given there is a team "foo"
    When I request the details of team "foo"
    Then I should be told that I don't have the authorization

  Scenario: Members can access the team details
    Given there is a team "foo"
    And the user "samuel" is user of the team "foo"
    When I request the details of team "foo"
    Then I should see the team details
