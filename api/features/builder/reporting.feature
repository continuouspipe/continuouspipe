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

  Scenario: The build is failed
    Given there is a build "00000000-0000-0000-0000-000000000000"
    Given the Docker build will fail because of something
    When I start the build "00000000-0000-0000-0000-000000000000"
    Then the published report should contain "error" for the key "status"

  Scenario: The build is successful
    Given there is a build "00000000-0000-0000-0000-000000000000"
    When I start the build "00000000-0000-0000-0000-000000000000"
    Then the published report should contain "success" for the key "status"

  Scenario: It adds the durations to the report
    Given the current datetime is "2017-03-02T12:00:00Z"
    And there is a build "00000000-0000-0000-0000-000000000000"
    When the current datetime is "2017-03-02T12:01:00Z"
    And I start the build "00000000-0000-0000-0000-000000000000"
    Then the published report should contain "60" for the key "duration.pending"

  Scenario: It adds details about the machine on which build is done
    When I create a build "00000000-0000-0000-0000-000000000000"
    And I start the build "00000000-0000-0000-0000-000000000000"
    Then the published report should contain the key "host.hostname"

  Scenario: It adds details about which engine was used for the build
    When I create a build "00000000-0000-0000-0000-000000000000"
    And I start the build "00000000-0000-0000-0000-000000000000"
    Then the published report should contain the key "engine"

  @elasticsearch
  Scenario: Reports should be published to elasticsearch
    Given I have configured elasticsearch
    When I create a build "00000000-0000-0000-0000-000000000000"
    And I start the build "00000000-0000-0000-0000-000000000000"
    Then the report should be published to elasticsearch

  @elasticsearch_secure
  Scenario: Reports should be published to elasticsearch securely
    Given I have configured elasticsearch to be talked to via SSL
    When I create a build "00000000-0000-0000-0000-000000000000"
    And I start the build "00000000-0000-0000-0000-000000000000"
    Then the report should be published to elasticsearch
