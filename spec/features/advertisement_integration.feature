Feature: Advertisement Integration and Revenue System
  In order to monetize the application while maintaining user experience
  As a system administrator
  I want to integrate various advertisement systems with proper privacy compliance

  # 1. Status: TODO
  # 2. Design: docs/diagrams/advertisement-integration-flow.md
  # 3. Test: tests/Feature/AdvertisementIntegrationTest.php
  # 4. Scenario Status Tracking:
  # | Scenario Name                    | Status        | Test Method                    | UI  | Backend |
  # |----------------------------------|---------------|--------------------------------|-----|---------|
  # | Google AdSense integration       | TODO          | test_google_adsense_integration| TODO| TODO    |
  # | Advertisement management         | TODO          | test_advertisement_management  | TODO| TODO    |
  # | Cookie consent handling          | TODO          | test_cookie_consent_handling   | TODO| TODO    |
  # | Ad position placement            | TODO          | test_ad_position_placement     | TODO| TODO    |
  # | Affiliate marketing links        | TODO          | test_affiliate_marketing_links | TODO| TODO    |
  # | Revenue tracking                 | TODO          | test_revenue_tracking          | TODO| TODO    |
  # | A/B testing for ad positions     | TODO          | test_ab_testing_ad_positions   | TODO| TODO    |
  # | Privacy compliance (GDPR/CCPA)   | TODO          | test_privacy_compliance        | TODO| TODO    |
  # | Mobile responsive ads            | TODO          | test_mobile_responsive_ads     | TODO| TODO    |
  # | Performance impact monitoring    | TODO          | test_performance_monitoring    | TODO| TODO    |

  Background:
    Given I am an administrator
    And the advertisement system is configured
    And privacy compliance is enabled

  Scenario: Google AdSense integration
    # 情境：Google AdSense 廣告整合
    Given I have configured Google AdSense with client ID
    When I visit the dashboard page
    Then I should see AdSense ads in designated positions
    And the ads should be responsive to screen size
    And ad impressions should be tracked for analytics

  Scenario: Advertisement management system
    # 情境：廣告管理系統
    Given I am on the admin advertisement management page
    When I create a new advertisement
    And I specify the position as "dashboard-top"
    And I set the content and duration
    Then the advertisement should be saved successfully
    And it should appear on the dashboard at the specified position
    When I deactivate the advertisement
    Then it should no longer appear on the frontend

  Scenario: Cookie consent handling
    # 情境：Cookie 同意處理
    Given I am a new visitor to the website
    When I first visit any page
    Then I should see a cookie consent banner
    When I click "Accept Cookies"
    Then personalized ads should be enabled
    And my consent should be stored
    When I click "Reject Cookies"
    Then only essential cookies should be used
    And no personalized ads should be shown

  Scenario: Strategic ad position placement
    # 情境：策略性廣告位置投放
    Given I am viewing the dashboard with tracked beers
    When the page loads
    Then I should see a top banner advertisement
    And I should see native ads between beer list items every 3 beers
    And I should see a bottom recommendation advertisement
    When I am on mobile device
    Then ad sizes should adjust to mobile dimensions
    And ads should not interfere with touch interactions

  Scenario: Affiliate marketing beer recommendations
    # 情境：聯盟行銷啤酒推薦
    Given I have added "Guinness Draught" to my collection
    When I view the beer details page
    Then I should see related beer purchase links
    And the links should contain proper affiliate tracking parameters
    When I click on an affiliate link
    Then it should redirect to the partner site
    And the click should be tracked for commission calculation

  Scenario: Revenue tracking and reporting
    # 情境：收益追蹤和報告
    Given advertisements have been displayed to users
    When I access the revenue reporting dashboard
    Then I should see daily, weekly, and monthly revenue reports
    And I should see click-through rates for each ad position
    And I should see affiliate commission earnings
    When I export the revenue data
    Then it should include detailed breakdown by advertisement type

  Scenario: A/B testing for ad positions
    # 情境：廣告位置 A/B 測試
    Given I have configured A/B test for ad positions
    When users visit the dashboard
    Then 50% should see ads in position A
    And 50% should see ads in position B
    When I review the A/B test results
    Then I should see conversion rates for each variation
    And I should be able to select the winning variation

  Scenario: GDPR/CCPA privacy compliance
    # 情境：GDPR/CCPA 隱私合規
    Given the privacy compliance system is active
    When a user from EU visits the site
    Then they should see GDPR-compliant cookie notice
    And they should have granular consent options
    When a user withdraws consent
    Then personalized advertising should stop immediately
    And their data should be anonymized according to regulations

  Scenario: Mobile responsive advertisement display
    # 情境：行動響應式廣告顯示
    Given I am using a mobile device
    When I browse any page with advertisements
    Then ads should resize automatically to fit the screen
    And ads should maintain proper aspect ratios
    And ads should not block essential UI elements
    When I rotate the device
    Then ads should adjust to the new orientation

  Scenario: Performance impact monitoring
    # 情境：效能影響監控
    Given advertisement systems are integrated
    When I monitor page load performance
    Then page load time should not increase by more than 200ms
    And Core Web Vitals should remain within acceptable thresholds
    When advertisement loading fails
    Then the page should still function normally
    And fallback content should be displayed

  Scenario: Sponsored content integration
    # 情境：贊助內容整合
    Given I have partnered with a beer brand
    When I create sponsored content for "Craft Beer Brand X"
    And I mark it as sponsored content
    Then it should appear in the beer recommendation section
    And it should be clearly labeled as "Sponsored"
    When users interact with sponsored content
    Then engagement metrics should be tracked separately

  Scenario: Ad blocker handling
    # 情境：廣告攔截器處理
    Given a user has an ad blocker enabled
    When they visit pages with advertisements
    Then the system should detect the ad blocker
    And display a polite message about supporting the site
    But core functionality should remain unaffected
    When the user disables the ad blocker
    Then advertisements should load normally

  Scenario: Advertisement analytics integration
    # 情境：廣告分析整合
    Given Google Analytics is configured
    When advertisements are displayed and clicked
    Then ad events should be tracked in Google Analytics
    And custom dimensions should include ad position and type
    When I review advertising reports
    Then I should see detailed performance metrics
    And I should be able to optimize based on user behavior patterns