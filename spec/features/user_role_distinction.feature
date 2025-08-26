Feature: User Role Distinction
  In order to manage the application effectively
  As an administrator
  I need to have different permissions than a regular user

  # 1. Status: DONE
  # 2. Design: docs/diagrams/user-role-distinction-flow.md
  # 3. Test: tests/Feature/AdminFeatureTest.php
  # 4. Scenario Status Tracking:
  # | Scenario Name                    | Status        | Test Method                    | UI  | Backend |
  # |----------------------------------|---------------|--------------------------------|-----|---------|
  # | Administrator views user list    | DONE          | test_admin_views_user_list    | DONE| DONE    |
  # | Administrator Access             | DONE          | test_administrator_access      | DONE| DONE    |
  # | Regular User Access              | DONE          | test_regular_user_access       | DONE| DONE    |

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

  # 場景: 一般使用者權限
  Scenario: Regular User Access
    Given I am a regular user
    When I attempt to visit the admin panel
    Then I should be redirected to the dashboard
    And I should see an "Unauthorized" message
