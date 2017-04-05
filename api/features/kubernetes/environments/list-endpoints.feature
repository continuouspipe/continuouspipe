Feature:
  In order to see the environment I've deployed
  As a user
  I want to be able to see the environments and their public endpoints

  Background:
    Given I am authenticated
    And the bucket of the team "my-team" is the bucket "00000000-0000-0000-0000-000000000000"
    And there is a cluster in the bucket "00000000-0000-0000-0000-000000000000" with the following configuration:
      | identifier | type       | address         | version | username | password |
      | my-cluster | kubernetes | https://1.2.3.4 | v1      | username | password |

  Scenario: It returns the ingress hosts
    Given there is a namespace "my-app"
    And there is a deployment "app" for the image "sroze/php-example"
    And there is an ingress "www" with the hostname "foo.continuouspipe.net" in the first rule with the following labels:
      | name                 | value |
      | component-identifier | app   |
    When I request the environment list of the cluster "my-cluster" of the team "my-team"
    Then the status of the component "app" should contain the public endpoint "foo.continuouspipe.net"
