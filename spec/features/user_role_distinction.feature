Feature: User Role Distinction
  In order to manage the application effectively
  As an administrator
  I need to have different permissions than a regular user

  # 1. Status: DONE
  # 2. Design: docs/diagrams/user-role-distinction-flow.md
  # 3. Test: tests/Feature/AdminFeatureTest.php
  # 4. Scenario Status Tracking:
# | Scenario Name                    | Status | Test Method                       | UI   | Backend |
# |----------------------------------|--------|-----------------------------------|------|---------|
# | Administrator views user list    | DONE   | admin_can_view_user_list          | DONE | DONE    |
# | Administrator Access             | DONE   | admin_can_access_admin_panel      | DONE | DONE    |
# | Regular User Access              | DONE   | regular_user_cannot_access_admin_panel | DONE | DONE    |
# | Guest Access Restriction        | DONE   | guest_cannot_access_admin_panel   | DONE | DONE    |
# | Example Admin Feature Test      | DONE   | test_example                      | DONE | DONE    |


  Scenario: Administrator views user list
    # Status: IMPLEMENTED
    Given I am an administrator
    When I navigate to the user management page
    Then I should see a list of all users
    And I should see "User Management" heading
    And for each user, I should see their email, name, and registration method
    And I should see users who registered via email, Google, and Apple
    And the registration provider should be clearly indicated

  Scenario: Administrator Access
    Given I am an administrator
    When I visit the admin panel dashboard
    Then I should be able to access it successfully
    And the page should load with status 200
    And I should see the administration interface

  # 場景: 一般使用者權限
  Scenario: Regular User Access
    Given I am a regular user with 'user' role
    When I attempt to visit the admin panel
    Then I should receive a 403 forbidden error
    And I should not be able to access admin features

  # 場景: 訪客權限
  Scenario: Guest Access Restriction
    Given I am not logged in
    When I try to access the admin panel
    Then I should be redirected to the login page
    And the response should have status 302
    And I should not see any admin content

  # 場景: 基本管理員功能測試
  Scenario: Example Admin Feature Test
    Given the admin feature system is set up
    When basic admin functionality is tested
    Then all basic tests should pass
    And the admin system should be working correctly
