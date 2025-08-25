Feature: Adding a New Beer to the Collection
  In order to expand my collection
  As a registered user
  I want to add a new type of beer that I have tasted.

  # Status: DONE
  # Test: tests/Feature/BeerCreationTest.php
  # UI: DONE
  # Backend: DONE

  # Scenario Status Tracking:
  # | Scenario Name                    | Status        | Test Method                    | UI  | Backend |
  # |----------------------------------|---------------|--------------------------------|-----|---------|
  # | Adding with brand suggestions    | DONE          | test_adding_with_suggestions   | DONE| DONE    |
  # | Adding with tasting note         | DONE          | test_adding_with_tasting_note  | DONE| DONE    |
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
