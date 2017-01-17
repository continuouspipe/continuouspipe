Feature:
  In order to be able to do the billing and such
  As a system
  I want to be able to track the activity of the users of each flow

  Background:
    Given I have a flow with UUID "00000000-0000-0000-0000-000000000000"

  Scenario: It published the commit activity of a user
    When the commit "sha1" is pushed to the branch "foo" by the user "samuel" with an email "samuel.roze@gmail.com"
    Then the commit activity of the user "samuel" on the flow "00000000-0000-0000-0000-000000000000" should have been dispatched

  Scenario: It published the commit activity of many users
    Given the commit "1234" has been written by the user "samuel" with an email "samuel.roze@gmail.com"
    And the commit "5678" has been written by the user "tony" with an email "tony@inviqa.com"
    When the commits "1234,5678" are pushed to the branch "foo"
    Then the commit activity of the user "samuel" on the flow "00000000-0000-0000-0000-000000000000" should have been dispatched
    And the commit activity of the user "tony" on the flow "00000000-0000-0000-0000-000000000000" should have been dispatched
