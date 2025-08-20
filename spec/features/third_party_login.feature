Feature: Third-Party Login
  In order to quickly access my account
  As a user
  I want to be able to log in using third-party services like Google or Apple.

  # Status: TODO

  Scenario: User logs in with Google
    Given I am on the login page
    When I click the "Login with Google" button
    Then I should be redirected to Google's authentication page
    And after successful authentication, I should be logged in to my account
    And my user profile should be created or updated with Google details

  Scenario: User logs in with Apple
    Given I am on the login page
    When I click the "Login with Apple" button
    Then I should be redirected to Apple's authentication page
    And after successful authentication, I should be logged in to my account
    And my user profile should be created or updated with Apple details

  Scenario: User with existing email logs in with third-party service
    Given I have an existing account with email "test@example.com"
    And I am on the login page
    When I click the "Login with Google" button
    Then I should be logged in to my existing account
    And my Google ID should be linked to my existing account
