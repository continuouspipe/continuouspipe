Feature:
  In order to override images' commands
  As a developer
  I want to be able to override container command

  Background:
    Given I am authenticated
    And I have a valid Kubernetes provider

  Scenario:
    When I send a deployment request from application template "overwrite-command"
    And pods are running for the replication controller "mysql"
    Then the component "mysql" should be deployed with the command "echo hello"
