Feature:
  In order to quickly have access to my pull-request on GitHub
  As a user
  I want to be able to have my pull-request in the relations

  Background:
    Given I am authenticated as "samuel"
    And the team "samuel" exists
    And I have a flow in the team "samuel"
    And the user "samuel" is "USER" of the team "samuel"
    And there is 1 application images in the repository

  Scenario: Get the pull-request links
    Given a tide is started
    And the GitHub pull-request #1 contains the tide-related commit
    When I ask the external relations
    Then I should see the GitHub pull-request #1
