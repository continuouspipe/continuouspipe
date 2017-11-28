Feature:
  In order to understand what is going on in my Kubernetes cluster
  As a user
  I want to have an overview of my running containers

  Background:
    Given there is a team "foo"
    And there is a bucket "00000000-0000-0000-0000-000000000000"
    And the user "samuel" is in the team "foo"

  Scenario: Authentication required
    Given I am authenticated as user "another-user"
    And the kube-status response for the path "/clusters/foo+cluster-identifier/history" will be a 200 response with the following body:
    """
    []
    """
    When I request the proxied kube-status path "/clusters/foo+cluster-identifier/history"
    Then I should be told that I don't have the authorization

  Scenario: A get the proxied response
    Given I am authenticated as user "samuel"
    And the kube-status response for the path "/clusters/foo+cluster-identifier/history" will be a 200 response with the following body:
    """
    []
    """
    When I request the proxied kube-status path "/clusters/foo+cluster-identifier/history"
    Then I should received a response 200 with the following content:
    """
    []
    """

  Scenario: Proxies the full cluster status
    Given I am authenticated as user "samuel"
    And the kube-status response for the path "/clusters/foo+cluster-identifier/status" will be a 200 response with the following body:
    """
    {"status": "OK"}
    """
    When I request the proxied kube-status path "/clusters/foo+cluster-identifier/status"
    Then I should received a response 200 with the following content:
    """
    {"status": "OK"}
    """

  Scenario: Proxies the entry history
    Given I am authenticated as user "samuel"
    And the kube-status response for the path "/clusters/foo+cluster-identifier/history/00000000-0000-0000-0000-000000000000" will be a 200 response with the following body:
    """
    {"nodes": []}
    """
    When I request the proxied kube-status path "/clusters/foo+cluster-identifier/history/00000000-0000-0000-0000-000000000000"
    Then I should received a response 200 with the following content:
    """
    {"nodes": []}
    """
