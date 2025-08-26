Feature: User Registration
  In order to access the main application features
  As a new user
  I want to be able to create an account

  # 1. Status: DONE
  # 2. Design: docs/diagrams/user-registration-flow.md
  # 3. Test: tests/Feature/RegistrationTest.php
  # 4. Scenario Status Tracking:
  # | Scenario Name                    | Status        | Test Method                    | UI  | Backend |
  # |----------------------------------|---------------|--------------------------------|-----|---------|
  # | New user registration           | DONE          | test_new_user_registration     | DONE| DONE    |
  # | Duplicate email registration    | DONE          | test_duplicate_email_registration| DONE| DONE    |

  Scenario: A new user registers with valid credentials
    Given I am on the registration page
    When I fill in "name" with "John Doe"
    And I fill in "email" with "john.doe@example.com"
    And I fill in "password" with "a_secure_password_123"
    And I fill in "password_confirmation" with "a_secure_password_123"
    And I press the "Register" button
    Then I should be redirected to the "/dashboard" page
    And I should see the welcome message "Welcome, John Doe"

  # 場景: 使用者嘗試使用已存在的信箱註冊
  Scenario: A user tries to register with an existing email
    Given a user with the email "existing.user@example.com" already exists
    When I try to register with the email "existing.user@example.com"
    Then I should see an error message indicating the email is already taken
