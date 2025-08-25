# Status: DONE
# Test: tests/Feature/DashboardTest.php
# UI: DONE
# Backend: DONE

# Scenario Status Tracking:
# | Scenario Name                    | Status        | Test Method                    | UI  | Backend |
# |----------------------------------|---------------|--------------------------------|-----|---------|
# | Empty state viewing              | DONE          | test_empty_state_viewing       | DONE| DONE    |
# | Sorted list with counts          | DONE          | test_sorted_list_with_counts   | DONE| DONE    |
Feature: Viewing the Beer List
  In order to see my collection
  As a registered user
  I want to view a list of all the beers I have tracked.

  Scenario: Viewing the beer list for the first time (empty state)
    Given I am logged in
    And I have not tracked any beers yet
    When I go to the beer tracking screen
    Then I should see a prominent button inviting me to "Track my first beer"
    And I should not see a list of beers

  # 場景: 查看已追蹤啤酒的排序列表及其計數
  Scenario: Viewing the sorted list of tracked beers with their counts
    Given I have tracked the following beers:
      | Brand     | Name      | Times Tasted | Last Tasted         |
      | Guinness  | Draught   | 5            | 2025-08-10 20:00:00 |
      | Brewdog   | Punk IPA  | 3            | 2025-08-12 21:00:00 |
      | Asahi     | Super Dry | 8            | 2025-08-11 18:00:00 |
    When I view my beer collection
    Then the beer list should be in the following order: "Punk IPA", "Asahi Super Dry", "Guinness Draught"
    And I should see "Punk IPA" with a tasting count of 3
    And I should see "Asahi Super Dry" with a tasting count of 8
    And I should see "Guinness Draught" with a tasting count of 5
