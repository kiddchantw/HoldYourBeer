Feature: Third-Party Login
  In order to quickly access my account
  As a user
  I want to be able to log in using third-party services like Google or Apple.

  # Status: IMPLEMENTATION INCOMPLETE
  # TODO: 需要去 Google Cloud Console 取得您自己的憑證
  # 1. 前往 https://console.cloud.google.com/
  # 2. 建立新專案或選擇現有專案
  # 3. 啟用 Google+ API
  # 4. 在「憑證」頁面建立 OAuth 2.0 用戶端 ID
  # 5. 取得 Client ID 和 Client Secret
  # 6. 設定授權的重新導向 URI (例如: http://localhost:8000/auth/google/callback)
  # 7. 更新 .env 檔案中的 GOOGLE_CLIENT_ID 和 GOOGLE_CLIENT_SECRET

  # Status: DONE (Test: tests/Feature/SocialLoginTest.php)

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

  # 場景: 已有帳號的使用者透過第三方服務登入
  Scenario: User with existing email logs in with third-party service
    Given I have an existing account with email "test@example.com"
    And I am on the login page
    When I click the "Login with Google" button
    Then I should be logged in to my existing account
    And my Google ID should be linked to my existing account
