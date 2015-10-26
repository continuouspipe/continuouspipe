Feature:
  In order to have containers with persistent volumes
  As a developer
  I want to be able to mount persistent volumes in my containers

  Background:
    Given I am authenticated
    And I have a valid Kubernetes provider

  Scenario: The volume claim should be created if do not exists
    When I send a deployment request from application template "persistent-mounted-volume"
    And pods are running for the replication controller "app"
    Then the volume claim "app-volume" should be created
    And the component "app" should be created with a persistent volume mounted in "/app/shared"

  Scenario: If the volume claim exists, it should reuse it
    Given there is a volume claim "app-volume"
    When I send a deployment request from application template "persistent-mounted-volume"
    And pods are running for the replication controller "app"
    Then the volume claim "app-volume" should not be created
    And the component "app" should be created with a persistent volume mounted in "/app/shared"
