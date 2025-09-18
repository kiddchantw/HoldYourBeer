Feature: Managing Tastings for a Beer
  In order to keep my tasting count accurate
  As a registered user
  I want to be able to increment and decrement the tasting count for a beer.

  # 1. Status: DONE
  # 2. Design: docs/diagrams/managing-tastings-flow.md
  # 3. Test: tests/Feature/TastingTest.php
  # 4. Scenario Status Tracking:
# | Scenario Name                                    | Status | Test Method                                     | UI   | Backend |
# |--------------------------------------------------|--------|-------------------------------------------------|------|---------|
# | It increments the tasting count and creates a log| DONE   | test_it_increments_the_tasting_count_and_creates_a_log | DONE | DONE    |
# | It decrements the tasting count and creates a log| DONE   | test_it_decrements_the_tasting_count_and_creates_a_log | DONE | DONE    |
# | It does not decrement below zero                | DONE   | test_it_does_not_decrement_below_zero           | DONE | DONE    |
# | Concurrent increments are handled correctly     | DONE   | test_concurrent_increments_are_handled_correctly| DONE | DONE    |
# | It can increment beer count (API)               | DONE   | it_can_increment_beer_count                     | DONE | DONE    |
# | It can decrement beer count (API)               | DONE   | it_can_decrement_beer_count                     | DONE | DONE    |
# | It cannot decrement below zero (API)            | DONE   | it_cannot_decrement_below_zero                  | DONE | DONE    |
# | It can get tasting logs (API)                   | DONE   | it_can_get_tasting_logs                         | DONE | DONE    |
# | It returns 404 for tasting logs of untracked beer| DONE   | it_returns_404_for_tasting_logs_of_untracked_beer| DONE | DONE    |


  Scenario: Incrementing the count of an existing beer
    Given I see "Guinness Draught" in my collection with a tasting count of 4
    When I press the "+1" button for "Guinness Draught"
    Then its tasting count should become 5
    And a new "increment" action should be in the tasting log for "Guinness Draught"

  # 場景: 透過減量來更正錯誤的增量
  Scenario: Correcting a mistaken increment by decrementing
    Given I see "Guinness Draught" in my collection with a tasting count of 5
    When I press the "-1" button for "Guinness Draught"
    Then its tasting count should become 4
    And a new "decrement" action should be in the tasting log for "Guinness Draught"

  # 場景: API 計數管理
  Scenario: Incrementing beer count via API
    Given I am authenticated via API
    And I have a beer in my collection with count 3
    When I send a POST request to increment the count
    Then the API should return success
    And the count should increase to 4
    And a tasting log should be created with action "increment"

  Scenario: Decrementing beer count via API
    Given I am authenticated via API
    And I have a beer in my collection with count 3
    When I send a POST request to decrement the count
    Then the API should return success
    And the count should decrease to 2
    And a tasting log should be created with action "decrement"

  Scenario: Preventing negative count via API
    Given I am authenticated via API
    And I have a beer in my collection with count 0
    When I send a POST request to decrement the count
    Then the API should return an error
    And the count should remain 0
    And no tasting log should be created

  Scenario: Retrieving tasting logs via API
    Given I am authenticated via API
    And I have a beer with some tasting history
    When I request the tasting logs for that beer
    Then I should receive a list of logs
    And each log should contain action, timestamp, and notes

  Scenario: Handling requests for untracked beer tasting logs
    Given I am authenticated via API
    And there is a beer I have never tasted
    When I request the tasting logs for that beer
    Then the API should return a 404 error
    And the error message should indicate the beer is not tracked
