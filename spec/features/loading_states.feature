Feature: Loading States and User Feedback
  In order to have a better user experience
  As a user
  I want to see loading indicators when actions are being processed

  # 1. Status: DONE
  # 2. Design: docs/diagrams/loading-states-flow.md
  # 3. Test: tests/Feature/LoadingStatesTest.php
  # 4. Scenario Status Tracking:
  # | Scenario Name                    | Status        | Test Method                    | UI  | Backend |
  # |----------------------------------|---------------|--------------------------------|-----|---------|
  # | Loading state when adding beer   | DONE          | test_loading_state_adding_beer | DONE| DONE    |
  # | Loading state when searching     | DONE          | test_loading_state_searching   | DONE| DONE    |
  # | Loading state when incrementing  | DONE          | test_loading_state_incrementing| DONE| DONE    |
  # | Loading state during navigation  | DONE          | test_loading_state_navigation  | DONE| DONE    |
  # | Loading state with error handling| DONE          | test_loading_state_error       | DONE| DONE    |
  # | Loading state for API calls      | DONE          | test_loading_state_api_calls   | DONE| DONE    |
  # | Loading state for image uploads  | DONE          | test_loading_state_image_upload| DONE| DONE    |

  Background:
    Given I am a registered user
    And I am logged into the application

  Scenario: Loading state when adding a new beer
    Given I am on the beer creation form
    When I fill in the beer details
    And I press the "Save" button
    Then I should see a loading spinner
    And the form should be disabled
    And I should see "Saving beer..." message
    When the beer is successfully saved
    Then the loading spinner should disappear
    And I should see a success message
    And the form should be re-enabled

  Scenario: Loading state when searching for brands
    Given I am on the beer creation form
    When I start typing in the brand search field
    Then I should see a small loading indicator in the search field
    When the search results are loaded
    Then the loading indicator should disappear
    And I should see the search suggestions

  Scenario: Loading state when incrementing beer count
    Given I am viewing my beer collection
    When I click the "+" button to increment a beer's count
    Then I should see a loading state on that specific beer item
    And the "+" button should be disabled
    When the count is successfully updated
    Then the loading state should disappear
    And the "+" button should be re-enabled
    And I should see the updated count

  Scenario: Loading state during page navigation
    Given I am on the dashboard
    When I click on a navigation link
    Then I should see a page loading indicator
    When the new page is fully loaded
    Then the loading indicator should disappear

  Scenario: Loading state with error handling
    Given I am on the beer creation form
    When I press the "Save" button
    And the server returns an error
    Then I should see a loading spinner initially
    When the error response is received
    Then the loading spinner should disappear
    And I should see an error message
    And the form should be re-enabled

  Scenario: Loading state for API calls
    Given I am using the mobile app
    When I make an API request to fetch my beer collection
    Then I should see a loading indicator in the response
    When the API response is complete
    Then the loading indicator should be removed
    And I should see the beer data

  # 場景: 圖片上傳的載入狀態
  Scenario: Loading state for image uploads
    Given I am on the beer creation form
    When I select an image file to upload
    And I press the "Upload" button
    Then I should see a progress bar
    And I should see "Uploading image..." message
    When the upload is complete
    Then the progress bar should disappear
    And I should see "Image uploaded successfully" message
