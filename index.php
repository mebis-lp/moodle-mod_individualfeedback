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
 * prints the overview of all individualfeedbacks included into the current course
 *
 * @author Andreas Grabs
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package mod_individualfeedback
 */

require_once("../../config.php");
require_once("lib.php");

$id = required_param('id', PARAM_INT);

$url = new moodle_url('/mod/individualfeedback/index.php', array('id'=>$id));

$PAGE->set_url($url);

if (!$course = $DB->get_record('course', array('id'=>$id))) {
    print_error('invalidcourseid');
}

$context = context_course::instance($course->id);

require_login($course);
$PAGE->set_pagelayout('incourse');

/*
// Trigger instances list viewed event.
$event = \mod_individualfeedback\event\course_module_instance_list_viewed::create(array('context' => $context));
$event->add_record_snapshot('course', $course);
$event->trigger();
*/

/// Print the page header
$strindividualfeedbacks = get_string("modulenameplural", "individualfeedback");
$strindividualfeedback  = get_string("modulename", "individualfeedback");

$PAGE->navbar->add($strindividualfeedbacks);
$PAGE->set_heading($course->fullname);
$PAGE->set_title(get_string('modulename', 'individualfeedback').' '.get_string('activities'));
echo $OUTPUT->header();
echo $OUTPUT->heading($strindividualfeedbacks);

/// Get all the appropriate data

if (! $individualfeedbacks = get_all_instances_in_course("individualfeedback", $course)) {
    $url = new moodle_url('/course/view.php', array('id'=>$course->id));
    notice(get_string('thereareno', 'moodle', $strindividualfeedbacks), $url);
    die;
}

$usesections = course_format_uses_sections($course->format);

/// Print the list of instances (your module will probably extend this)

$timenow = time();
$strname  = get_string("name");
$strresponses = get_string('responses', 'individualfeedback');

$table = new html_table();

if ($usesections) {
    $strsectionname = get_string('sectionname', 'format_'.$course->format);
    if (has_capability('mod/individualfeedback:viewreports', $context)) {
        $table->head  = array ($strsectionname, $strname, $strresponses);
        $table->align = array ("center", "left", 'center');
    } else {
        $table->head  = array ($strsectionname, $strname);
        $table->align = array ("center", "left");
    }
} else {
    if (has_capability('mod/individualfeedback:viewreports', $context)) {
        $table->head  = array ($strname, $strresponses);
        $table->align = array ("left", "center");
    } else {
        $table->head  = array ($strname);
        $table->align = array ("left");
    }
}


foreach ($individualfeedbacks as $individualfeedback) {
    //get the responses of each individualfeedback
    $viewurl = new moodle_url('/mod/individualfeedback/view.php', array('id'=>$individualfeedback->coursemodule));

    if (has_capability('mod/individualfeedback:viewreports', $context)) {
        $completed_individualfeedback_count = intval(individualfeedback_get_completeds_group_count($individualfeedback));
    }

    $dimmedclass = $individualfeedback->visible ? '' : 'class="dimmed"';
    $link = '<a '.$dimmedclass.' href="'.$viewurl->out().'">'.$individualfeedback->name.'</a>';

    if ($usesections) {
        $tabledata = array (get_section_name($course, $individualfeedback->section), $link);
    } else {
        $tabledata = array ($link);
    }
    if (has_capability('mod/individualfeedback:viewreports', $context)) {
        $tabledata[] = $completed_individualfeedback_count;
    }

    $table->data[] = $tabledata;

}

echo "<br />";

echo html_writer::table($table);

/// Finish the page

echo $OUTPUT->footer();

