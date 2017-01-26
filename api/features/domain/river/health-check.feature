Feature:
  In order to reflect the application's health
  As an application
  I want to answer my status to an HTTP request

  Scenario: It answer 200 on `/` because of GKE's ingresses
    Given I send a GET request to the path "/"
    Then the status code of the response should be 200
