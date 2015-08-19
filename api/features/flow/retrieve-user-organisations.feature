Feature:
  In order to choose a source to list repositories from
  As a developer
  I need to be able to retrieve a list of user's organisation

  Scenario:
    Given I am a member of the following organisations:
      | organisation |
      | inviqa       |
      | sensio       |
    And I am authenticated
    When I send a request to list my organisations
    Then I should receive the following list of organisations:
      | organisation |
      | inviqa       |
      | sensio       |
