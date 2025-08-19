Feature: Brand Analytics Charts and Consumption Patterns
  In order to understand my drinking preferences by brand
  As a registered user
  I want to view charts and analytics about my beer consumption by brand

  # Status: TODO
  # Test: tests/Feature/BrandAnalyticsChartsTest.php (需要建立)
  # UI: TODO (需要實作圖表元件和 tab 介面)
  # Backend: TODO (需要實作 Larapex Charts 整合)

  Background:
    Given I am a registered user
    And I am logged into the application
    And I have consumed several beers from different brands

  Scenario: Viewing brand preference chart in analytics tab
    Given I am on the dashboard
    When I click on the "Analytics" tab
    Then I should see a chart titled "My Favorite Beer Brands"
    And the chart should display brand names on the X-axis
    And the chart should display consumption count on the Y-axis
    And the chart should be sorted by consumption count (highest to lowest)

  Scenario: Chart displays correct data from user's brand consumption
    Given I have consumed 5 "Guinness" beers
    And I have consumed 3 "Brewdog" beers
    And I have consumed 1 "Asahi" beer
    When I navigate to the analytics tab
    Then the chart should show "Guinness" with a value of 5
    And the chart should show "Brewdog" with a value of 3
    And the chart should show "Asahi" with a value of 1

  Scenario: Chart updates when new beers are added to existing brands
    Given I am viewing the brand preference chart
    And the chart shows "Guinness" with 5 beers
    When I add a new "Guinness" beer to my collection
    Then the chart should automatically update
    And "Guinness" should now show 6 beers
    And the chart should maintain proper sorting

  Scenario: Chart handles empty data gracefully
    Given I am a new user with no beer consumption
    When I navigate to the analytics tab
    Then I should see a message "No brand consumption data available"
    And the chart area should show a placeholder state

  Scenario: Chart is responsive and mobile-friendly
    Given I am viewing the analytics tab on a mobile device
    When I rotate the device to landscape mode
    Then the chart should resize appropriately
    And all chart elements should remain visible and readable

  Scenario: Chart allows interaction and tooltips
    Given I am viewing the brand preference chart
    When I hover over a chart bar
    Then I should see a tooltip showing the exact count
    And the tooltip should display the brand name and count
    When I click on a chart bar
    Then I should see detailed information about that brand

  Scenario: Chart supports different chart types
    Given I am on the analytics tab
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
    When I navigate to the analytics tab
    Then the chart should have proper ARIA labels
    And the chart data should be accessible via keyboard navigation
    And color contrast should meet accessibility standards
