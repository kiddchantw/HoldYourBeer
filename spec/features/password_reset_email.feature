Feature: Password Reset Email Functionality
  # 功能：密碼重設信件功能
  In order to recover my account access
  As a user who forgot my password
  I want to receive a password reset email

  # Status: TODO
  # Test: tests/Feature/PasswordResetEmailTest.php (需要建立)
  # UI: 忘記密碼頁面 (已實作)
  # Backend: PasswordResetLinkController (已實作)

  Background:
    Given I am on the forgot password page
    And I am not logged into the application

  Scenario: Successfully request password reset email
    # 情境：成功請求密碼重設信件
    Given I am on the forgot password page
    When I enter my registered email address
    And I press the "Send Password Reset Link" button
    Then I should see a loading spinner
    And the form should be disabled
    When the email is successfully sent
    Then the loading spinner should disappear
    And I should see "We have emailed your password reset link!" message
    And the form should be re-enabled
    And a password reset email should be sent to my email address

  Scenario: Password reset email contains correct information
    # 情境：密碼重設信件包含正確資訊
    Given a password reset email has been sent to my email
    When I open the email
    Then I should see "Reset Password Notification" in the subject
    And I should see my name in the email body
    And I should see a reset password button or link
    And the reset link should contain a valid token
    And the reset link should expire within 60 minutes

  Scenario: Password reset email for non-existent email
    # 情境：非註冊信箱的密碼重設處理
    Given I am on the forgot password page
    When I enter an email address that is not registered
    And I press the "Send Password Reset Link" button
    Then I should see a loading spinner
    When the request is processed
    Then the loading spinner should disappear
    And I should see "We have emailed your password reset link!" message
    And no email should actually be sent (security measure)

  Scenario: Rate limiting for password reset requests
    # 情境：密碼重設請求的速率限制
    Given I have already requested a password reset email
    When I try to request another password reset email within 1 minute
    Then I should see a loading spinner
    When the rate limit is enforced
    Then the loading spinner should disappear
    And I should see "Please wait before retrying" message
    And no additional email should be sent

  Scenario: Password reset email with special characters in email
    # 情境：包含特殊字元的信箱地址
    Given I am on the forgot password page
    When I enter an email address with special characters (e.g., user+tag@domain.com)
    And I press the "Send Password Reset Link" button
    Then I should see a loading spinner
    When the email is successfully sent
    Then the loading spinner should disappear
    And I should see "We have emailed your password reset link!" message
    And the email should be sent to the correct address

  Scenario: Password reset email delivery failure handling
    # 情境：密碼重設信件寄送失敗處理
    Given I am on the forgot password page
    When I enter my registered email address
    And I press the "Send Password Reset Link" button
    And the email service is temporarily unavailable
    Then I should see a loading spinner
    When the email delivery fails
    Then the loading spinner should disappear
    And I should see "Unable to send password reset email. Please try again later." message
    And the form should be re-enabled

  Scenario: Password reset email for inactive account
    # 情境：非活躍帳戶的密碼重設處理
    Given I have an inactive account
    When I request a password reset
    Then I should see a loading spinner
    When the request is processed
    Then the loading spinner should disappear
    And I should see "We have emailed your password reset link!" message
    And no email should be sent to inactive accounts

  Scenario: Password reset email audit logging
    # 情境：密碼重設信件的審計日誌
    Given I am on the forgot password page
    When I request a password reset email
    Then the system should log the password reset request
    And the log should include my email address
    And the log should include the timestamp
    And the log should include the IP address
