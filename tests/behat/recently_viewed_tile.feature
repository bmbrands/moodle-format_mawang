@format @format_mawang
Feature: Recently viewed tile functionality in mawang format
  In order to see which section I last visited
  As a student
  I need to see a "Recently viewed" indicator on the last visited tile

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format | coursedisplay | numsections |
      | Course 1 | C1        | mawang | 1             | 4           |
    And the following "activities" exist:
      | activity | name           | intro                  | course | idnumber | section |
      | page     | Page Section 1 | Page in section 1      | C1     | page1    | 1       |
      | page     | Page Section 2 | Page in section 2      | C1     | page2    | 2       |
      | page     | Page Section 3 | Page in section 3      | C1     | page3    | 3       |
      | page     | Page Section 4 | Page in section 4      | C1     | page4    | 4       |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student |
    And I log in as "student1"
    And I am on "Course 1" course homepage

  @javascript
  Scenario: Recently viewed indicator appears on last visited tile
    When I should see "Topic 1" in the "Topic 1" tile
    And I should see "Topic 2" in the "Topic 2" tile
    And I should see "Topic 3" in the "Topic 3" tile
    And I should see "Topic 4" in the "Topic 4" tile
    And I should not see "Recently viewed" in any tile
    When I click on tile for section "2"
    And I should see "Page Section 2"
    And I click on "Course 1" "link"
    Then I should see "Recently viewed" in tile for section "2"
    And I should not see "Recently viewed" in tile for section "1"
    And I should not see "Recently viewed" in tile for section "3"
    And I should not see "Recently viewed" in tile for section "4"

  @javascript
  Scenario: Recently viewed indicator moves to new tile when visiting different section
    Given I click on tile for section "1"
    And I click on "Course 1" "link"
    And I should see "Recently viewed" in tile for section "1"
    When I click on tile for section "3"
    And I click on "Course 1" "link"
    Then I should see "Recently viewed" in tile for section "3"
    And I should not see "Recently viewed" in tile for section "1"
    And I should not see "Recently viewed" in tile for section "2"
    And I should not see "Recently viewed" in tile for section "4"

  @javascript
  Scenario: Multiple students have independent recently viewed tracking
    Given I click on tile for section "2"
    And I click on "Course 1" "link"
    And I should see "Recently viewed" in tile for section "2"
    And I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    Then I should not see "Recently viewed" in any tile
    When I click on tile for section "4"
    And I click on "Course 1" "link"
    Then I should see "Recently viewed" in tile for section "4"
    And I should not see "Recently viewed" in tile for section "1"
    And I should not see "Recently viewed" in tile for section "2"
    And I should not see "Recently viewed" in tile for section "3"
