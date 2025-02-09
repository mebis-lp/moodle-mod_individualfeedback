<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Upgrade helper functions
 *
 * @package   mod_individualfeedback
 * @copyright 2016 Marina Glancy
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Fill new field courseid in tables individualfeedback_completed or indfeedback_completedtmp
 *
 * @param bool $tmp use for temporary table
 */
function mod_individualfeedback_upgrade_courseid($tmp = false) {
    global $DB;
    $suffix = $tmp ? 'tmp' : '';
    // Name is shortened for individual feedback.
    $tablecompleted = $tmp ? 'indfeedback_completedtmp' : 'individualfeedback_completed';

    // Part 1. Ensure that each completed record has associated values with only one courseid.
    $sql = "SELECT c.id
        FROM {{$tablecompleted}} c, {individualfeedback_value$suffix} v
        WHERE c.id = v.completed
        GROUP by c.id
        having count(DISTINCT v.course_id) > 1";
    $problems = $DB->get_fieldset_sql($sql);
    foreach ($problems as $problem) {
        $courses = $DB->get_fieldset_sql("SELECT DISTINCT course_id "
                . "FROM {individualfeedback_value$suffix} WHERE completed = ?", array($problem));
        $firstcourse = array_shift($courses);
        $record = $DB->get_record($tablecompleted, array('id' => $problem));
        unset($record->id);
        $DB->update_record($tablecompleted, ['id' => $problem, 'courseid' => $firstcourse]);
        foreach ($courses as $courseid) {
            $record->courseid = $courseid;
            $completedid = $DB->insert_record($tablecompleted, $record);
            $DB->execute("UPDATE {individualfeedback_value$suffix} SET completed = ? WHERE completed = ? AND course_id = ?",
                    array($completedid, $problem, $courseid));
        }
    }

    // Part 2. Update courseid in the completed table.
    if ($DB->get_dbfamily() !== 'mysql') {
        $sql = "UPDATE {{$tablecompleted}} "
            . "SET courseid = (SELECT COALESCE(MIN(v.course_id), 0) "
            . "FROM {individualfeedback_value$suffix} v "
            . "WHERE v.completed = {{$tablecompleted}}.id)";
        $DB->execute($sql);
    } else {
        $sql = "UPDATE {{$tablecompleted}} c, {individualfeedback_value$suffix} v "
            . "SET c.courseid = v.course_id "
            . "WHERE v.completed = c.id AND v.course_id <> 0";
        $DB->execute($sql);
    }
}

/**
 * Ensure tables individualfeedback_value and individualfeedback_valuetmp have unique entries for each pair (completed,item).
 *
 * @param bool $tmp use for temporary table
 */
function mod_individualfeedback_upgrade_delete_duplicate_values($tmp = false) {
    global $DB;
    $suffix = $tmp ? 'tmp' : '';

    $sql = "SELECT MIN(id) AS id, completed, item, course_id " .
            "FROM {individualfeedback_value$suffix} GROUP BY completed, item, course_id HAVING count(id)>1";
    $records = $DB->get_records_sql($sql);
    foreach ($records as $record) {
        $DB->delete_records_select("individualfeedback_value$suffix",
            "completed = :completed AND item = :item AND course_id = :course_id AND id > :id", (array)$record);
    }
}
