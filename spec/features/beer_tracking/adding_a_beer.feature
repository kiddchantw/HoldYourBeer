Feature: Adding a New Beer to the Collection
  In order to expand my collection
  As a registered user
  I want to add a new type of beer that I have tasted.

  # Status: DONE (後端 API 已由 tests/Feature/BeerCreationTest.php 覆蓋)
  # UI: DONE (UI 已由 app/Livewire/CreateBeer.php 和 resources/views/livewire/create-beer.blade.php 實現)
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
