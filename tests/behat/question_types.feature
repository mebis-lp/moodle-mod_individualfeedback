@mod @mod_individualfeedback
Feature: Test creating different types of individualfeedback questions for anonymous individualfeedback
  In order to create individualfeedbacks
  As a teacher
  I need to be able to add different question types

  @javascript
  Scenario: Create different types of questions in anonymous individualfeedback with javascript enabled
    Given the following "users" exist:
      | username | firstname | lastname |
      | teacher1 | Teacher   | 1        |
      | student1 | Student   | 1        |
      | student2 | Student   | 2        |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
    And the following "activities" exist:
      | activity   | name                | course | idnumber    |
      | individualfeedback   | Learning experience | C1     | individualfeedback0   |
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Learning experience"
    And I click on "Edit questions" "link" in the ".nav-tabs" "css_element"
    And I add a "Information" question to the individualfeedback with:
      | Question         | this is an information question |
      | Label            | info                            |
      | Information type | Course                          |
    And I add a "Label" question to the individualfeedback with:
      | Contents | label text |
    And I add a "Longer text answer" question to the individualfeedback with:
      | Question         | this is a longer text answer |
      | Label            | longertext                   |
    And I add a "Multiple choice" question to the individualfeedback with:
      | Question         | this is a multiple choice 1 |
      | Label            | multichoice1                |
      | Multiple choice values | option a\noption b\noption c  |
    And I add a "Multiple choice (rated)" question to the individualfeedback with:
      | Question               | this is a multiple choice rated |
      | Label                  | multichoice4                    |
      | Multiple choice type   | Multiple choice - single answer |
      | Multiple choice values | 0/option k\n1/option l\n5/option m |
    And I add a "Numeric answer" question to the individualfeedback with:
      | Question               | this is a numeric answer |
      | Label                  | numeric                  |
      | Range from             | 0                        |
      | Range to               | 100                      |
    And I add a "Short text answer" question to the individualfeedback with:
      | Question               | this is a short text answer |
      | Label                  | shorttext                   |
      | Maximum characters accepted | 200                    |
    And I follow "Logout"
    And I press "Continue"
    
    When I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Learning experience"
    And I change window size to "large"
    And I follow "Answer the questions..."
    And I set the following fields to these values:
      | this is a longer text answer | my long answer |
      | option b                     | 1              |
      | option l                     | 1              |
      | this is a numeric answer (0 - 100) | 35       |
      | this is a short text answer  | hello          |
    And I press "Submit your answers"
    And I follow "Logout"
    And I press "Continue"
    
    When I log in as "student2"
    And I am on "Course 1" course homepage
    And I follow "Learning experience"
    And I follow "Answer the questions..."
    And I set the following fields to these values:
      | this is a longer text answer | lots of individualfeedbacks |
      | option a                     | 1              |
      | option m                     | 1              |
      | this is a numeric answer (0 - 100) | 71       |
      | this is a short text answer  | no way         |
    And I press "Submit your answers"
    And I follow "Logout"
    And I press "Continue"
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Learning experience"
    Then I should see "Submitted answers: 2"
    And I should see "Questions: 6"
    And I click on "Evaluations" "link" in the ".nav-tabs" "css_element"
    Then I should see "C1" in the "(info)" "table"
    And I should see "my long answer" in the "(longertext)" "table"
    And I should see "lots of individualfeedbacks" in the "(longertext)" "table"
    And I show chart data for the "multichoice4" individualfeedback
    And I should see "0" in the "option k" "table_row"
    And I should not see "%" in the "(0) option k" "table_row"
    And I should see "1 (50.00 %)" in the "(1) option l" "table_row"
    And I should see "1 (50.00 %)" in the "(5) option m" "table_row"
    And I should see "Average: 3"
    And I should see "35" in the "(numeric)" "table"
    And I should see "71" in the "(numeric)" "table"
    And I should see "Average: 53" in the "(numeric)" "table"
    And I should see "no way" in the "(shorttext)" "table"
    And I should see "hello" in the "(shorttext)" "table"
    And I change window size to "medium"
    And I change window size to "large"
    And I wait "2" seconds
    And I show chart data for the "multichoice1" individualfeedback
    And I should see "1 (50 %)" in the "option a" "table_row"
    And I should see "1 (50 %)" in the "option b" "table_row"
