Feature:
  In order to ensure the best user experience
  As an ops engineer
  I want to identify the behaviour of the client's build on our infrastructure

  Scenario: It publishes a report when a build is created
    When I create a build "00000000-0000-0000-0000-000000000000"
    Then a report should be published
    And the published report should contain "pending" for the key "status"

  Scenario: The build is started
    Given there is a build "00000000-0000-0000-0000-000000000000"
    When I start the build "00000000-0000-0000-0000-000000000000"
    Then the published report should contain "running" for the key "status"
