Feature: Brand Analytics Charts and Consumption Patterns
  In order to understand my drinking preferences by brand
  As a registered user
  I want to view charts and analytics about my beer consumption by brand

  # Status: DONE
  # Test: tests/Feature/BrandAnalyticsChartsTest.php
  # UI: DONE (已實作 Chart.js 圖表元件和導覽列按鈕)
  # Backend: DONE (已實作 ChartsController 數據處理)

  Background:
    Given I am a registered user
    And I am logged into the application
    And I have consumed several beers from different brands

  Scenario: Accessing charts from navigation menu
    Given I am logged into the application
    When I look at the navigation menu
    Then I should see a "Charts" navigation link
    When I click on the "Charts" navigation link
    Then I should be taken to the charts page

  Scenario: Viewing brand preference chart
    Given I am on the charts page
    Then I should see a pie chart
    And the chart should display brand names in the legend
    And the chart slices should represent the consumption count
    And the chart should display percentages for each slice

  Scenario: Chart displays correct data from user's brand consumption
    Given I have consumed 5 "Guinness" beers
    And I have consumed 3 "Brewdog" beers
    And I have consumed 1 "Asahi" beer
    When I navigate to the charts page
    Then the chart should show "Guinness" with a value of 5
    And the chart should show "Brewdog" with a value of 3
    And the chart should show "Asahi" with a value of 1

  Scenario: Chart updates when new beers are added to existing brands
    Given I am viewing the brand preference chart
    And the chart shows "Guinness" with 5 beers
    When I add a new "Guinness" beer to my collection
    And I refresh the charts page
    Then the chart should show "Guinness" with 6 beers

  Scenario: Chart handles empty data gracefully
    Given I am a new user with no beer consumption
    When I navigate to the charts page
    Then I should see a message "No brand consumption data available"
    And the chart area should show a placeholder state

  Scenario: Chart is responsive and mobile-friendly
    Given I am viewing the charts page on a mobile device
    When I rotate the device to landscape mode
    Then the chart should resize appropriately
    And all chart elements should remain visible and readable

  Scenario: Chart allows interaction and tooltips
    Given I am viewing the brand preference chart
    When I hover over a chart slice
    Then I should see a tooltip showing the exact count
    And the tooltip should display the brand name and count

  Scenario: Chart supports different chart types
    Given I am on the charts page
    When I click on the "Chart Type" dropdown
    Then I should see options for "Bar Chart", "Pie Chart", "Line Chart"
    When I select "Pie Chart"
    Then the chart should change to a pie chart format
    And the data should be displayed as percentages

  Scenario: Chart data export functionality
    Given I am viewing the brand preference chart
    When I click on the "Export" button
    Then I should see export options for "PNG", "PDF", "CSV"
    When I select "PNG"
    Then the chart should be downloaded as a PNG image file

  Scenario: Chart accessibility features
    Given I am using a screen reader
    When I navigate to the charts page
    Then the chart should have proper ARIA labels
    And the chart data should be accessible via keyboard navigation
    And color contrast should meet accessibility standards

  # 場景: 當前頁面的導覽連結會正確突顯
  Scenario: Navigation link is properly highlighted when active
    Given I am on the charts page
    Then the "Charts" navigation link should be highlighted as active
    And the URL should reflect the charts route
