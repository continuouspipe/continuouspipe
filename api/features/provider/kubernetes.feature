Feature:
  In order to deploy on Kubernetes clusters
  As a devops
  I need to manage Kubernetes providers

  Background:
    Given I am authenticated

  Scenario: I can create a kubernetes provider
    When I send a provider creation request for type "kubernetes" with body:
    """
    {
      "identifier": "example",
      "cluster": {
        "address": "foo",
        "version": "v1"
      },
      "user": {
        "username": "samuel",
        "password": "secret"
      }
    }
    """
    Then the Kubernetes cloud provider must be successfully saved
