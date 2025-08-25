Feature: Viewing the Tasting History for a Beer
  In order to see the timeline of my beer tasting
  As a registered user
  I want to view a detailed history for each beer in my collection.

  # Status: IN_PROGRESS
  # Test: tests/Feature/ViewingTastingHistoryTest.php
  # UI: IN_PROGRESS
  # Backend: DONE

  # Scenario Status Tracking:
  # | Scenario Name                    | Status        | Test Method                    | UI  | Backend |
  # |----------------------------------|---------------|--------------------------------|-----|---------|
  # | Viewing tasting history          | IN_PROGRESS   | test_viewing_tasting_history  | IN_PROGRESS| DONE    |

  Background:
    Given I have a beer "Kirin Lager" in my collection
    And I have the following tasting history for "Kirin Lager":
      | action      | created_at       | note                                                 |
      | initial     | 2025-08-20 10:00 | A crisp and refreshing lager, perfect for a summer day. |
      | increment   | 2025-08-21 18:30 |                                                      |

  # 場景: 查看品飲歷史
  Scenario: Viewing the tasting history
    Given I am on the dashboard
    When I click on the "Kirin Lager" beer card
    Then I should be on the tasting history page for "Kirin Lager"
    And I should see a timeline with 2 entries
    And the first entry should be "initial" at "2025-08-20 10:00" with the note "A crisp and refreshing lager, perfect for a summer day."
    And the second entry should be "increment" at "2025-08-21 18:30"
