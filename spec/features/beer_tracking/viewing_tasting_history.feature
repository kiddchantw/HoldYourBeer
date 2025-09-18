Feature: Viewing the Tasting History for a Beer
  In order to see the timeline of my beer tasting
  As a registered user
  I want to view a detailed history for each beer in my collection.

  # 1. Status: DONE
  # 2. Design: docs/diagrams/viewing-tasting-history-flow.md
  # 3. Test: tests/Feature/ViewingTastingHistoryTest.php
  # 4. Scenario Status Tracking:
# | Scenario Name                                   | Status | Test Method                               | UI   | Backend |
# |-------------------------------------------------|--------|-------------------------------------------|------|---------|
# | A user can view the tasting history for a beer | DONE   | test_a_user_can_view_the_tasting_history_for_a_beer | DONE | DONE    |
# | It can get tasting logs (API)                  | DONE   | it_can_get_tasting_logs                  | DONE | DONE    |
# | It returns 404 for tasting logs of untracked beer | DONE   | it_returns_404_for_tasting_logs_of_untracked_beer | DONE | DONE    |


  Background:
    Given I have a beer "Kirin Lager" in my collection
    And I have the following tasting history for "Kirin Lager":
      | action      | created_at       | note                                                 |
      | initial     | 2025-08-20 10:00 | A crisp and refreshing lager, perfect for a summer day. |
      | increment   | 2025-08-21 18:30 |                                                      |

  # 場景: 查看品飲歷史
  Scenario: A user can view the tasting history for a beer
    Given I am on the dashboard
    When I click on the "Kirin Lager" beer card
    Then I should be on the tasting history page for "Kirin Lager"
    And I should see a timeline with 2 entries
    And the first entry should be "initial" at "August 20, 2025" with the note "A crisp and refreshing lager, perfect for a summer day."
    And the second entry should be "increment" at "August 21, 2025"
    And I should see the beer name "Kirin Lager" displayed correctly

  # 場景: 透過 API 取得品飲記錄
  Scenario: Getting tasting logs via API
    Given I am authenticated via API
    And I have a beer in my collection with tasting logs
    When I request the tasting logs for that beer via API
    Then I should receive a successful response
    And the response should contain the tasting log entries
    And each entry should include action, timestamp, and note fields
    And the logs should be properly formatted

  # 場景: 請求未追蹤啤酒的品飲記錄
  Scenario: Requesting tasting logs for untracked beer via API
    Given I am authenticated via API
    And there is a beer I have never tracked
    When I request the tasting logs for that untracked beer
    Then I should receive a 404 error response
    And the error should indicate that the beer is not being tracked
