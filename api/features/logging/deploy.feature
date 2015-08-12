Feature:
  In order to debug the failing tides
  As a developer
  I need to be able to see detailed logs of the river

  Scenario:
    Given I have a flow with a deploy task
    When a tide is started based on that workflow
    Then a "Deploying environment" log should be created

  Scenario:
    Given I have a flow with a deploy task
    When a tide is started based on that workflow
    And the deployment succeed
    Then the "Deploying environment" log should be successful

  Scenario:
    Given I have a flow with a deploy task
    When a tide is started based on that workflow
    And the deployment failed
    Then the "Deploying environment" log should be failed
