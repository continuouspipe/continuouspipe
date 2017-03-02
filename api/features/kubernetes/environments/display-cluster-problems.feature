Feature:
  In order to have an overview of the deployed environments
  As a developer
  I want to have the list and the status of each environment deployed in different namespaces

  Scenario:
    Given I am authenticated
    And the bucket of the team "my-team" is the bucket "00000000-0000-0000-0000-000000000000"
    And there is a cluster in the bucket "00000000-0000-0000-0000-000000000000" with the following configuration:
      | identifier | type       | address         | version | username | password |
      | my-cluster | kubernetes | https://1.2.3.4 | v1.4    | username | password |
    And I am building a deployment request
    And the target environment name is "my-environment"
    And the target cluster identifier is "my-cluster"
    And the credentials bucket is "00000000-0000-0000-0000-000000000000"
    And the specification come from the template "simple-app"
    And the environment label "flow" contains "1234567890"
    And the environment label "tide" contains "0987654321"
    And the pods of the deployments will be running after creation
    And the cluster with the address "1.2.3.4" will have the following problems:
      | category            | message                   |
      | schedulable_cpu_low | This is serious my friend |
    And I send the built deployment request
    And the deployment should be successful
    Then I should see a "text" log event in the log stream with message "Found 1 problem with the cluster"
    Then I should see a "text" log event in the log stream with message "This is serious my friend"
