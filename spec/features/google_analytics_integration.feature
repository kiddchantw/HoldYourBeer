Feature: Google Analytics Integration
  In order to track user behavior and application performance
  As a system administrator
  I want to integrate Google Analytics for comprehensive analytics

  # 1. Status: TODO
  # 2. Design: docs/diagrams/google-analytics-integration-flow.md
  # 3. Test: tests/Feature/GoogleAnalyticsIntegrationTest.php
  # 4. Scenario Status Tracking:
  # | Scenario Name                    | Status        | Test Method                    | UI  | Backend |
  # |----------------------------------|---------------|--------------------------------|-----|---------|
  # | Page view tracking               | TODO          | test_page_view_tracking        | TODO| TODO    |
  # | User authentication tracking     | TODO          | test_user_authentication_tracking| TODO| TODO    |
  # | Beer creation tracking           | TODO          | test_beer_creation_tracking    | TODO| TODO    |
  # | Beer count increment tracking   | TODO          | test_beer_count_increment_tracking| TODO| TODO    |
  # | Search behavior tracking         | TODO          | test_search_behavior_tracking  | TODO| TODO    |
  # | Error tracking                   | TODO          | test_error_tracking            | TODO| TODO    |
  # | User engagement tracking         | TODO          | test_user_engagement_tracking | TODO| TODO    |
  # | Conversion funnel tracking       | TODO          | test_conversion_funnel_tracking| TODO| TODO    |
  # | Performance monitoring           | TODO          | test_performance_monitoring    | TODO| TODO    |

  Background:
    Given Google Analytics is properly configured
    And the tracking code is loaded on all pages
    And I am a registered user

  Scenario: Page view tracking
    # 情境：頁面瀏覽追蹤 - 基本頁面訪問統計
    Given I am on the dashboard page
    When the page loads completely
    Then a page view event should be sent to Google Analytics
    And the event should include the page title "Dashboard"
    And the event should include the current URL
    And the event should include the user's session ID

  Scenario: User authentication tracking
    # 情境：用戶認證追蹤 - 登入行為分析
    Given I am on the login page
    When I successfully log in with valid credentials
    Then a login event should be sent to Google Analytics
    And the event should include the user ID (anonymized)
    And the event should include the login method "email"
    And the event should include the timestamp

  Scenario: Beer creation tracking
    # 情境：啤酒建立追蹤 - 核心功能使用統計
    Given I am on the beer creation form
    When I successfully create a new beer
    Then a custom event should be sent to Google Analytics
    And the event should be labeled "beer_created"
    And the event should include the beer brand name
    And the event should include the beer type
    And the event should include the user's beer count

  Scenario: Beer count increment tracking
    # 情境：啤酒計數增加追蹤 - 用戶互動行為
    Given I am viewing my beer collection
    When I increment a beer's count
    Then a custom event should be sent to Google Analytics
    And the event should be labeled "beer_count_incremented"
    And the event should include the beer ID
    And the event should include the new count value
    And the event should include the previous count value

  Scenario: Search behavior tracking
    # 情境：搜尋行為追蹤 - 搜尋功能使用分析
    Given I am on the beer creation form
    When I search for a brand name
    Then a search event should be sent to Google Analytics
    And the event should include the search query
    And the event should include the number of results
    And the event should include the search duration

  Scenario: Error tracking
    # 情境：錯誤追蹤 - 表單驗證錯誤統計
    Given I am on a form page
    When I submit the form with invalid data
    Then an error event should be sent to Google Analytics
    And the event should include the error type "validation_error"
    And the event should include the form name
    And the event should include the specific error messages

  Scenario: User engagement tracking
    # 情境：用戶參與度追蹤 - 頁面停留時間分析
    Given I am on any page of the application
    When I spend more than 30 seconds on the page
    Then an engagement event should be sent to Google Analytics
    And the event should include the time spent on page
    And the event should include the page URL
    And the event should include the user's interaction count

  Scenario: Conversion funnel tracking
    # 情境：轉換漏斗追蹤 - 用戶註冊和首次使用
    Given I am a new user
    When I complete the registration process
    Then a conversion event should be sent to Google Analytics
    And the event should be labeled "user_registration_completed"
    And the event should include the registration source
    And the event should include the time to complete registration
    When I create my first beer
    Then another conversion event should be sent
    And the event should be labeled "first_beer_created"

  Scenario: Performance monitoring
    # 情境：效能監控 - 頁面載入速度追蹤
    Given I am loading a page
    When the page load time exceeds 3 seconds
    Then a performance event should be sent to Google Analytics
    And the event should include the page load time
    And the event should include the page URL
    And the event should include the user's device type

  Scenario: Mobile vs desktop tracking
    # 情境：行動裝置與桌面追蹤 - 跨裝置使用分析
    Given I am accessing the application from a mobile device
    When I perform any action
    Then the event should include device category "mobile"
    And the event should include the screen resolution
    And the event should include the user agent information

  Scenario: Custom dimension tracking
    # 情境：自訂維度追蹤 - 用戶分層和屬性分析
    Given I am a premium user
    When I perform any action
    Then the event should include custom dimension "user_tier" = "premium"
    And the event should include custom dimension "account_age" in days
    And the event should include custom dimension "beer_collection_size"

  Scenario: E-commerce tracking for beer purchases
    # 情境：啤酒購買的電子商務追蹤 - 啤酒購買行為分析
    Given I am viewing a beer in a partner store
    When I click on the purchase link
    Then an e-commerce event should be sent to Google Analytics
    And the event should include the beer name as product name
    And the event should include the store name as affiliation
    And the event should include the price if available

  Scenario: A/B testing tracking
    # 情境：A/B 測試追蹤 - 實驗組對比分析
    Given I am part of an A/B test group
    When I view a page with test variations
    Then the event should include custom dimension "ab_test_group"
    And the event should include custom dimension "variation_id"
    And the event should include custom dimension "test_name"

  Scenario: Privacy compliance tracking
    # 情境：隱私合規追蹤 - GDPR 合規性
    Given I have not consented to analytics tracking
    When I visit the application
    Then no tracking events should be sent to Google Analytics
    And the tracking code should be disabled
    When I consent to analytics tracking
    Then tracking should be enabled
    And a consent event should be sent to Google Analytics
