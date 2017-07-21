Feature:
  In order to chose the best flow option for me
  As a user
  I want to know if I can activate flex on my flow

  Background:
    Given I am authenticated as "samuel"
    And there is a team "samuel"
    And the user "samuel" is "USER" of the team "samuel"
    And I have a flow with UUID "00000000-0000-0000-0000-000000000000" in the team "samuel"

  Scenario: Flex cannot be activated
    When I request the features of the flow "00000000-0000-0000-0000-000000000000"
    Then the feature "flex" should not be available
    And the feature "flex" should not be enabled

  Scenario: Flex can be activated
    Given the code repository contains the fixtures folder "flex-skeleton"
    When I request the features of the flow "00000000-0000-0000-0000-000000000000"
    Then the feature "flex" should be available
    Then the feature "flex" should not be enabled

  Scenario: Flex is enabled
    Given the code repository contains the fixtures folder "flex-skeleton"
    And the flow "00000000-0000-0000-0000-000000000000" has flex activated
    When I request the features of the flow "00000000-0000-0000-0000-000000000000"
    Then the feature "flex" should be available
    Then the feature "flex" should be enabled
