Feature:
  In order to deploy the environment to a set of clusters
  As a user
  I want to be able to give the cluster identifier from my team's bucket

  Background:
    Given I am authenticated
    And I am building a deployment request
    And the target environment name is "my-environment"
    And the credentials bucket is "00000000-0000-0000-0000-000000000000"
    And there is a cluster in the bucket "00000000-0000-0000-0000-000000000000" with the following configuration:
      | identifier | type       | address         | version | username | password |
      | my-cluster | kubernetes | https://1.2.3.4 | v1.3    | username | password |

  Scenario: The cluster do no exists, it fails with a meaningful error message
    Given the specification come from the template "simple-private-app"
    And the target cluster identifier is "another-cluster"
    When I send the built deployment request
    Then the deployment should be failed
    And I should see a text log event in the log stream with message 'The cluster "another-cluster" is not found in your project'
