Feature: User Registration
  In order to access the main application features
  As a new user
  I want to be able to create an account

  # 1. Status: DONE
  # 2. Design: docs/diagrams/user-registration-flow.md
  # 3. Test: tests/Feature/RegistrationTest.php
  # 4. Scenario Status Tracking:
# | Scenario Name                                 | Status | Test Method                               | UI   | Backend |
# |-----------------------------------------------|--------|-------------------------------------------|------|---------|
# | Registration screen can be rendered           | DONE   | test_registration_screen_can_be_rendered  | DONE | DONE    |
# | A new user can register                       | DONE   | test_a_new_user_can_register              | DONE | DONE    |
# | New users can register (Auth version)         | DONE   | test_new_users_can_register               | DONE | DONE    |
# | A user cannot register with an existing email| DONE   | test_a_user_cannot_register_with_existing_email | DONE | DONE    |


  Scenario: Registration screen can be rendered
    Given I visit the registration page
    When I load the page
    Then I should see the registration form
    And I should see name, email, and password fields
    And I should see a "Register" button

  Scenario: A new user registers with valid credentials
    Given I am on the registration page
    When I fill in "name" with "John Doe"
    And I fill in "email" with "john.doe@example.com"
    And I fill in "password" with "a_secure_password_123"
    And I fill in "password_confirmation" with "a_secure_password_123"
    And I press the "Register" button
    Then I should be redirected to the "/dashboard" page
    And I should be authenticated
    And I should see the welcome message "Welcome, John Doe"

  # 場景: 新使用者註冊（驗證版本）
  Scenario: New users can register (Auth verification)
    Given I am on the registration page
    When I submit valid registration data
    Then I should be authenticated after registration
    And I should be redirected to the dashboard
    And my account should be created in the system

  # 場景: 使用者嘗試使用已存在的信箱註冊
  Scenario: A user tries to register with an existing email
    Given a user with the email "existing.user@example.com" already exists
    When I try to register with the email "existing.user@example.com"
    Then I should see an error message indicating the email is already taken
    And the registration should fail
    And I should remain on the registration page
