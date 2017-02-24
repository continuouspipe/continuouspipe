Feature:
  In order to try Continuous Pipe features before it's available to everybody
  As a user
  I want to be able to log in by providing early access codes

  Scenario: Log in with early access code
    When I open the link of the early access program and enter the code "CODE-001"
    And the browser is redirected to the login page
    And the user "geza" try to authenticate himself with GitHub
    Then the authentication should be successful

  Scenario: Invalid early access code is rejected
    When I open the link of the early access program and enter the code "INVALID-CODE"
    Then I should see an error on the page
