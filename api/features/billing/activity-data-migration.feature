Feature:
  In order to have user activity data in MySQL
  As an administrator
  I want to be able to migrate the data

  @smoke
  Scenario: Run CLI command to migrate the data
    Given Redis has the following user activities:
      | team_slug | flow_uuid                             | type | user | date_time  |
      | bar       | 00000000-0000-0000-0000-000000000001  | push | geza | now        |
    When I run the migration console command "app:migrate:user_activities"
    Then the user activity of the user "geza" should have been tracked
