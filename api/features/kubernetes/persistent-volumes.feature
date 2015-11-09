Feature:
  In order to have containers with persistent volumes
  As a developer
  I want to be able to mount persistent volumes in my containers

  Background:
    Given I am authenticated
    And there is a cluster in the bucket "00000000-0000-0000-0000-000000000000" with the following configuration:
      | identifier | type       | address         | version | username | password |
      | my-cluster | kubernetes | https://1.2.3.4 | v1      | username | password |
    And I am building a deployment request
    And the target environment name is "my-environment"
    And the target cluster identifier is "my-cluster"
    And the credentials bucket is "00000000-0000-0000-0000-000000000000"

  Scenario: The volume claim should be created if do not exists
    Given the specification come from the template "persistent-mounted-volume"
    When I send the built deployment request
    And pods are running for the replication controller "app"
    Then the volume claim "app-volume" should be created
    And the component "app" should be created with a persistent volume mounted in "/app/shared"

  Scenario: If the volume claim exists, it should reuse it
    Given there is a volume claim "app-volume"
    And the specification come from the template "persistent-mounted-volume"
    When I send the built deployment request
    And pods are running for the replication controller "app"
    Then the volume claim "app-volume" should not be created
    And the component "app" should be created with a persistent volume mounted in "/app/shared"
