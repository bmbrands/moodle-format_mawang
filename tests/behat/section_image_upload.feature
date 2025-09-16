@format @format_mawang @_file_upload
Feature: Section image upload functionality in mawang format
  In order to enhance visual presentation of course sections
  As a teacher
  I need to be able to upload and display images on section tiles

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email            |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format | coursedisplay | numsections |
      | Course 1 | C1        | mawang | 0             | 4           |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |

  @javascript
  Scenario: Teacher can upload an image to a section tile
    Given I am on the "Course 1" course page logged in as teacher1
    And I turn editing mode on
    When I edit the section "1"
    And I upload "course/format/mawang/tests/fixtures/section-image.png" file to "Section image" filemanager
    And I press "Save changes"
    And I am on the "Course 1" course page
    And I turn editing mode off
    Then I should see the section image in tile for section "1"
    And I should see the section image in tile for section "1"

  @javascript
  Scenario: Student can see uploaded section image on tile
    Given I am on the "Course 1" course page logged in as teacher1
    And I turn editing mode on
    And I edit the section "2"
    And I upload "course/format/mawang/tests/fixtures/section-image.png" file to "Section image" filemanager
    And I press "Save changes"
    And I turn editing mode off
    When I am on the "Course 1" course page logged in as student1
    Then I should see the section image in tile for section "2"

  @javascript
  Scenario: Section image displays correctly in tile layout
    Given I am on the "Course 1" course page logged in as teacher1
    And I turn editing mode on
    And I edit the section "1"
    And I set the following fields to these values:
      | Section name | Visual Section |
    And I upload "course/format/mawang/tests/fixtures/section-image.png" file to "Section image" filemanager
    And I press "Save changes"
    And I turn editing mode off
    And I am on the "Course 1" course page
    Then I should see "Visual Section" in the "Visual Section" tile
    And I should see the section image in tile for section "1"
