Feature: Adding a New Beer to the Collection
  In order to expand my collection
  As a registered user
  I want to add a new type of beer that I have tasted.

  # 1. Status: DONE
  # 2. Design: docs/diagrams/beer-adding-flow.md
  # 3. Test: tests/Feature/BeerCreationTest.php
  # 4. Scenario Status Tracking:
# | Scenario Name                                      | Status | Test Method                                    | UI   | Backend |
# |----------------------------------------------------|--------|-----------------------------------------------|------|---------|
# | A user can successfully add a beer                 | DONE   | a_user_can_successfully_add_a_beer            | DONE | DONE    |
# | It returns a validation error if name is missing   | DONE   | it_returns_a_validation_error_if_name_is_missing | DONE | DONE    |
# | It returns an unauthorized error if user is not authenticated | DONE   | it_returns_an_unauthorized_error_if_user_is_not_authenticated | DONE | DONE    |
# | It returns a validation error if brand id does not exist | DONE   | it_returns_a_validation_error_if_brand_id_does_not_exist | DONE | DONE    |
# | It sets the tasting count to 1 when a new beer is added | DONE   | it_sets_the_tasting_count_to_1_when_a_new_beer_is_added | DONE | DONE    |
# | Create page is rendered for authenticated user (Controller) | DONE   | test_create_page_is_rendered_for_authenticated_user | DONE | DONE    |
# | Guest is redirected from create page (Controller) | DONE   | test_guest_is_redirected_from_create_page     | DONE | DONE    |
# | It can store a new beer and brand (Controller)    | DONE   | test_it_can_store_a_new_beer_and_brand        | DONE | DONE    |

  Scenario: Adding a new beer with brand and series suggestions
    Given I am on the beer tracking screen
    And the following brands already exist in the system: "Guinness", "Brewdog", "Asahi"
    And the brand "Brewdog" has an existing series named "Hazy Jane"
    When I press the "Add a new beer" button
    And I start typing "Brew" in the brand field
    Then I should see "Brewdog" as a suggestion
    When I select the brand "Brewdog"
    And I start typing "Hazy" in the series field
    Then I should see "Hazy Jane" as a suggestion
    When I fill in the rest of the details and save
    Then the new beer should be added to my collection with a tasting count of 1

  # 場景: 新增啤酒並附上評論
  Scenario: Adding a new beer with a tasting note
    Given I am on the "Add a new beer" page
    When I fill in the brand as "Kirin"
    And I fill in the name as "Lager"
    And I write "A crisp and refreshing lager, perfect for a summer day." in the tasting note
    And I save the new beer
    Then the beer "Kirin Lager" should be in my collection
    And it should have a tasting note "A crisp and refreshing lager, perfect for a summer day."

  # 場景: API 驗證錯誤處理
  Scenario: Validation error when beer name is missing
    Given I am authenticated as a user
    When I submit a beer creation request without a name
    Then I should receive a 422 validation error
    And the error should indicate that name is required

  Scenario: Unauthorized access when user is not authenticated
    Given I am not authenticated
    When I try to create a beer via the API
    Then I should receive a 401 unauthorized error

  Scenario: Validation error when brand ID does not exist
    Given I am authenticated as a user
    When I submit a beer creation request with a non-existent brand ID
    Then I should receive a 422 validation error
    And the error should indicate that brand_id is invalid

  Scenario: Initial tasting count is set correctly
    Given I am authenticated as a user
    When I successfully create a new beer
    Then the beer should be added to the database
    And my tasting count for that beer should be set to 1

  # 場景: Web 介面存取控制
  Scenario: Beer creation form access control
    Given I am an authenticated user
    When I visit the beer creation page
    Then I should see the creation form
    But when I am not logged in
    And I try to visit the beer creation page
    Then I should be redirected to the login page

  Scenario: Complete beer and brand creation workflow
    Given I am authenticated as a user
    When I submit valid beer data including a new brand
    Then both the brand and beer should be created
    And I should be redirected to the dashboard
    And I should see a success message
    And my tasting count should be initialized to 1
