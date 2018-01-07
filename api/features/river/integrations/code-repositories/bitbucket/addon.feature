Feature:
  In order to have a direct access to the Bitbucket repositories
  As a system
  I want to match Atlassian's add-on mechanism

  @smoke
  Scenario: Save add-on installation
    When the add-on "connection:659577" is installed for the user account "sroze"
    Then the installation should be saved

  Scenario: Uninstall an add-on
    Given there is the add-on "connection:67890" installed for the user account "sroze"
    When the add-on "connection:67890" is uninstalled for the user account "sroze"
    Then the add-on "connection:67890" should be removed

  Scenario: Uses the correct installation to sign the request
    Given there is the add-on "connection:12345" installed for the user account "foo"
    And there is the add-on "connection:67890" installed for the user account "sroze"
    When I create a client for the BitBucket repository "sroze/php-example"
    Then the client should use the JWT token of the addon "connection:67890"

  Scenario: Cannot create a client without installation
    Given there is the add-on "connection:12345" installed for the user account "foo"
    When I create a client for the BitBucket repository "sroze/php-example"
    Then the client should not be created because of the missing add-on installation
