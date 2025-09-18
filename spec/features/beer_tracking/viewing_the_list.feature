Feature: Viewing the Beer List
  In order to see my collection
  As a registered user
  I want to view a list of all the beers I have tracked.

  # 1. Status: DONE
  # 2. Design: docs/diagrams/viewing-beer-list-flow.md
  # 3. Test: tests/Feature/DashboardTest.php
  # 4. Scenario Status Tracking:
# | Scenario Name                                     | Status | Test Method                                   | UI   | Backend |
# |---------------------------------------------------|--------|-----------------------------------------------|------|---------|
# | It displays the dashboard with a list of beers   | DONE   | test_it_displays_the_dashboard_with_a_list_of_beers | DONE | DONE    |
# | It displays the empty state when there are no beers | DONE   | test_it_displays_the_empty_state_when_there_are_no_beers | DONE | DONE    |
# | Create page is rendered for authenticated user    | DONE   | test_create_page_is_rendered_for_authenticated_user | DONE | DONE    |
# | Guest is redirected from create page             | DONE   | test_guest_is_redirected_from_create_page     | DONE | DONE    |


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
    Then the beer list should be in the following order: "Punk IPA", "Super Dry", "Draught"
    And I should see "Punk IPA" with a tasting count of 3
    And I should see "Super Dry" with a tasting count of 8
    And I should see "Draught" with a tasting count of 5

  # 場景: 已驗證使用者可以存取建立啤酒頁面
  Scenario: Authenticated user can access beer creation page
    Given I am logged in as a user
    When I visit the beer creation page
    Then I should see the beer creation form
    And the page should load successfully
    And I should see fields for beer details

  # 場景: 訪客使用者會被導向登入頁面
  Scenario: Guest user is redirected to login page
    Given I am not logged in
    When I try to access the beer creation page
    Then I should be redirected to the login page
    And I should not see the beer creation form
