@format @format_mawang
Feature: Duration calculation functionality in mawang format
  In order to see the total duration of activities in each section
  As a teacher
  I need to add activities with duration values and see the total displayed on section tiles

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email            |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format | coursedisplay | numsections |
      | Course 1 | C1        | mawang | 1             | 3           |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And I ensure mawang custom fields are set up

  @javascript
  Scenario: Teacher adds activities with duration and sees total on tiles
    Given I am on the "Course 1" course page logged in as teacher1
    And I turn editing mode on

    # Add 2 page activities to section 1
    When I add a "page" activity to course "Course 1" section "1" and I fill the form with:
      | Name         | Page Activity 1 |
      | Description  | First page      |
      | Page content | Content 1       |
      | Duration     | 5               |
    And I am on the "Course 1" course page
    And I add a "page" activity to course "Course 1" section "1" and I fill the form with:
      | Name         | Page Activity 2 |
      | Description  | Second page     |
      | Page content | Content 2       |
      | Duration     | 10              |
    And I am on the "Course 1" course page

    # Add 3 page activities to section 2
    And I add a "page" activity to course "Course 1" section "2" and I fill the form with:
      | Name         | Page Activity 3 |
      | Description  | Third page      |
      | Page content | Content 3       |
      | Duration     | 5               |
    And I am on the "Course 1" course page
    And I add a "page" activity to course "Course 1" section "2" and I fill the form with:
      | Name         | Page Activity 4 |
      | Description  | Fourth page     |
      | Page content | Content 4       |
      | Duration     | 10              |
    And I am on the "Course 1" course page
    And I add a "page" activity to course "Course 1" section "2" and I fill the form with:
      | Name         | Page Activity 5 |
      | Description  | Fifth page      |
      | Page content | Content 5       |
      | Duration     | 8               |
    And I am on the "Course 1" course page

    # Set duration for section 1 activities
    When I am on the "Course 1" course page
    And I turn editing mode off
    Then I should see duration total "15" in tile for section "1"
    And I should see duration total "23" in tile for section "2"
