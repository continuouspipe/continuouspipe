Feature:
  In order to be able to bill the teams
  As a system
  I want to be able to track and display the user activities

  Scenario: I track the activities
    When I receive the following "user_activity" message:
    """
    {"team_slug": "foo", "flow_uuid": "00000000-0000-0000-0000-000000000000", "type": "push", "date_time": "2017-01-23T10:02:46+00:00", "user": {"username": "sroze", "email": "no-reply@github.com"}}
    """
    Then the user activity of the user "sroze" should have been tracked

  @smoke
  Scenario: I can get the activity once stored
    Given I receive the following "user_activity" message:
    """
    {"team_slug": "foo", "flow_uuid": "00000000-0000-0000-0000-000000000000", "type": "push", "date_time": "2017-01-23T10:02:46+00:00", "user": {"username": "sroze", "email": "no-reply@github.com"}}
    """
    When I request the activity of the team "foo" between "2017-01-23T10:02:46+00:00" and "2017-01-23T10:02:46+00:00"
    Then I should see the activity of the user "sroze"
