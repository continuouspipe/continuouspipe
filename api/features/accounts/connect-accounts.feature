Feature:
  In order to allow ContinuousPipe to integrate with my favorite tools
  As a user
  I want to be able to connect external accounts

  @smoke
  Scenario: I can unlink a connected account
    Given I have a connected GitHub account "3768f8e0-4b6d-4e21-8a09-435647566750" for the user "sroze"
    When I unlink the account "3768f8e0-4b6d-4e21-8a09-435647566750" from the user "sroze"
    Then the account "3768f8e0-4b6d-4e21-8a09-435647566750" should not be linked to the user "sroze"
