Feature: User Role Distinction

  # Status: DONE
  In order to manage the application effectively
  As an administrator
  I need to have different permissions than a regular user

  Scenario: Administrator views user list
    # Status: IMPLEMENTED
    Given I am an administrator
    And I am on the admin dashboard page
    When I navigate to the "User Management" tab
    Then I should see a list of all users
    And for each user, I should see their email, name, and registration method (e.g., "email", "Google", "Apple")

  Scenario: Administrator Access
    Given I am an administrator
    When I visit the admin panel
    Then I should see the administration options

  Scenario: Regular User Access
    Given I am a regular user
    When I attempt to visit the admin panel
    Then I should be redirected to the dashboard
    And I should see an "Unauthorized" message
