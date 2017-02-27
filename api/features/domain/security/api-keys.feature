Feature:
  In order to allow user-based access to the API
  As an API
  I want to accept and understand users' API

  Scenario: I can authenticate on the API with my API key
    Given there is a user "samuel"
    And the team "my-team" exists
    And the user "samuel" have the API key "12345"
    And I have a flow with UUID "00000000-0000-0000-0000-000000000000" in the team "my-team"
    When I retrieve the list of the flows of the team "my-team" with the API key "12345"
    Then I should see the flow "00000000-0000-0000-0000-000000000000"

  Scenario: I won't be authenticated with a wrong API key
    Given there is a user "samuel"
    And the team "my-team" exists
    And the user "samuel" have the API key "12345"
    When I retrieve the list of the flows of the team "my-team" with the API key "098765"
    Then I should be told that I am not authenticated
