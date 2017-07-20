Feature:
  In order to build the application with the generated configuration
  As a system
  I need to make these inceptions in the code files sent to the builder

  To achieve that, we will re-package and re-stream the code archives on the river side.

  Background:
    Given I have a flow with UUID "00000000-0000-0000-0000-000000000000"
    And the code archive of the flow "00000000-0000-0000-0000-000000000000" looks like the fixtures file "flex-skeleton-master.tar.gz"

  Scenario: It do not do anything with non-flex flows
    When I request the archive of the repository for the flow "00000000-0000-0000-0000-000000000000" and reference "sha1"
    Then the archive should not contain a "Dockerfile" file

  Scenario: Flexified flow will add the Dockerfile
    Given the flow "00000000-0000-0000-0000-000000000000" has flex activated
    When I request the archive of the repository for the flow "00000000-0000-0000-0000-000000000000" and reference "sha1"
    Then the archive should contain a "Dockerfile" file
