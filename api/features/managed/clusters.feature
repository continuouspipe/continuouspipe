Feature:
  In order to use the managed infrastructure CP offers
  As a user
  I want to be able to have a cluster in my project I can use, that is this managed cluster

  Background:
    Given there is a bucket "00000000-0000-0000-0000-000000000000"
    And there is a team "my-company" with the credentials bucket "00000000-0000-0000-0000-000000000000"
    And the user "samuel" is administrator of the team "my-company"
    And there is a billing profile "00000000-0000-0000-0000-000000000000"
    And the team "my-company" is linked to the billing profile "00000000-0000-0000-0000-000000000000"

  Scenario: I can create a managed cluster as an administrator
    Given the billing profile "00000000-0000-0000-0000-000000000000" have the plan "lean"
    And I am authenticated as user "samuel"
    When I request a managed cluster to be created for the team "my-company"
    And I ask the list of the clusters in the bucket "00000000-0000-0000-0000-000000000000"
    Then the list should contain the cluster "managed"
