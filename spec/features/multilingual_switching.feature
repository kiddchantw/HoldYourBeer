Feature: Multilingual Language Switching
  In order to use the application in my preferred language
  As a user
  I want to switch between Chinese and English

  # Status: TODO
  # Design: DONE (docs/multilingual_switching_design.md)
  # Test: tests/Feature/MultilingualSwitchingTest.php (需要建立)
  # UI: 語言切換元件 (需要實作)
  # Backend: 多語系支援 (需要實作)

  Background:
    Given I am on any page of the application
    And the current language is set to English

  Scenario: Switch language from English to Chinese
    # 情境：從英文切換到中文
    Given I am viewing the page in English
    When I click on the language switcher
    And I select "中文" from the language options
    Then the page should reload
    And all text should be displayed in Chinese
    And the language switcher should show "English" as an option
    And my language preference should be saved

  Scenario: Switch language from Chinese to English
    # 情境：從中文切換到英文
    Given I am viewing the page in Chinese
    When I click on the language switcher
    And I select "English" from the language options
    Then the page should reload
    And all text should be displayed in English
    And the language switcher should show "中文" as an option
    And my language preference should be saved

  Scenario: Language preference persistence across sessions
    # 情境：語言偏好設定在會話間持續保存
    Given I have set my language preference to Chinese
    When I log out of the application
    And I log back in
    Then the application should remember my language preference
    And all text should be displayed in Chinese

  Scenario: Language switching on different page types
    # 情境：在不同頁面類型上切換語言
    Given I am on the dashboard page
    When I switch the language to Chinese
    Then the dashboard should display in Chinese
    When I navigate to the beer creation page
    Then the beer creation page should also display in Chinese
    And the language preference should be maintained

  Scenario: Language switcher presence on main pages
    # 情境：主要頁面必須有語言切換器
    Given I am on the home page "http://holdyourbeer.test/"
    Then I should see a language switcher component in the navigation
    And I should see a language switcher component in the footer
    When I navigate to the dashboard "http://holdyourbeer.test/dashboard"
    Then I should also see a language switcher component in the navigation
    And I should also see a language switcher component in the footer
    And the language switcher should be consistently positioned
    And the language switcher should show the current language

  Scenario: Language switching with form validation messages
    # 情境：表單驗證訊息的語言切換
    Given I am on a form page in English
    When I submit the form with invalid data
    Then I should see validation error messages in English
    When I switch the language to Chinese
    Then the validation error messages should be displayed in Chinese
    And the form should maintain its current state

  Scenario: Language switching with dynamic content
    # 情境：動態內容的語言切換
    Given I am viewing a list of beers in English
    When I switch the language to Chinese
    Then the beer names should remain unchanged
    But the labels, buttons, and navigation should be in Chinese
    And the data should not be affected by the language change

  Scenario: Language switching accessibility
    # 情境：語言切換的無障礙功能
    Given I am using a screen reader
    When I switch the language
    Then the screen reader should announce the language change
    And all interactive elements should have proper language attributes
    And the focus should be maintained on the language switcher

  Scenario: Language switching with browser language detection
    # 情境：瀏覽器語言自動偵測
    Given my browser is set to Chinese
    When I first visit the application
    Then the application should automatically detect my preferred language
    And display content in Chinese by default
    And the language switcher should show "English" as an option

  Scenario: Language switching with URL prefix
    # 情境：透過 URL 前綴切換語言
    Given I am on the "/dashboard" page in English
    When I navigate to the "/zh-TW/dashboard" URL
    Then the page should display all content in Chinese
    And the language switcher should reflect the current language

  Scenario: Language switching with AJAX requests
    # 情境：AJAX 請求的語言切換
    Given I am on a page in Chinese
    When I perform an AJAX action
    Then the response should include Chinese text
    When I switch to English and perform the same action
    Then the response should include English text
    And the language context should be maintained in API calls

  Scenario: Language switching on home page and dashboard
    # 情境：首頁和儀表板的語言切換功能
    Given I am on the home page "http://holdyourbeer.test/"
    And the current language is English
    When I switch the language to Chinese
    Then the home page should display in Chinese
    And the welcome message should be in Chinese
    And the navigation menu should be in Chinese
    When I navigate to the dashboard "http://holdyourbeer.test/dashboard"
    Then the dashboard should also display in Chinese
    And the dashboard title should be in Chinese
    And the beer collection summary should be in Chinese
    And the language switcher should show "English" as an option
    When I switch back to English on the dashboard
    Then the dashboard should display in English
    And the language preference should be saved
    When I return to the home page
    Then the home page should also display in English
    And the language preference should be consistent across pages
