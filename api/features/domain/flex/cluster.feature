Feature:
  In order to have an hassle-free first time experience
  As a user
  I want CP to configure my cluster automatically

  Background:
    Given there is a team "sam"
    And there is a user "sam"
    And the user "sam" is "ADMIN" of the team "sam"
    And I have a flow with UUID "00000000-0000-0000-0000-000000000000" in the team "sam"
    And I am authenticated as "sam"

  Scenario: It creates a flex cluster when I activate flex
    When I activate flex for the flow "00000000-0000-0000-0000-000000000000"
    Then the team "sam" should have a cluster named "flex"

  Scenario: It keeps the existing flex cluster if already exists
    Given I have a flow with UUID "11111111-0000-0000-0000-000000000000" in the team "sam"
    And I activate flex for the flow "11111111-0000-0000-0000-000000000000"
    When I activate flex for the flow "00000000-0000-0000-0000-000000000000"
    Then the team "sam" should have one cluster named "flex"
