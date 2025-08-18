# Status: DONE
# Test: tests/Feature/TastingTest.php
Feature: Managing Tastings for a Beer
  In order to keep my tasting count accurate
  As a registered user
  I want to be able to increment and decrement the tasting count for a beer.

  Scenario: Incrementing the count of an existing beer
    Given I see "Guinness Draught" in my collection with a tasting count of 4
    When I press the "+1" button for "Guinness Draught"
    Then its tasting count should become 5
    And a new "increment" action should be in the tasting log for "Guinness Draught"

  Scenario: Correcting a mistaken increment by decrementing
    Given I see "Guinness Draught" in my collection with a tasting count of 5
    When I press the "-1" button for "Guinness Draught"
    Then its tasting count should become 4
    And a new "decrement" action should be in the tasting log for "Guinness Draught"