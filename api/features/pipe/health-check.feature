Feature:
  In order to have zero downtime
  As an orchestrator of the application
  I want to be able to have insight's on the application's health

  # Mostly because of GKE's Ingresses, we need to have the health-check at
  # the path `/`
  Scenario: The home page should return a 200
    When I request the page at "/"
    Then the response status code should be 200
