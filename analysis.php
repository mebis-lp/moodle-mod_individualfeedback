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
 * shows an analysed view of individualfeedback based on subtab
 *
 * @copyright Andreas Grabs
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package mod_individualfeedback
 */

require_once("../../config.php");
require_once("lib.php");

$current_tab = 'analysis';

$id = required_param('id', PARAM_INT);  // Course module id.
$currentsubtab = optional_param('subtab', 'detail_questions', PARAM_TEXT);

$url = new moodle_url('/mod/individualfeedback/analysis.php', array('id' => $id, 'subtab' => $currentsubtab));
$PAGE->set_url($url);

list($course, $cm) = get_course_and_cm_from_cmid($id, 'individualfeedback');
require_course_login($course, true, $cm);

$individualfeedback = $PAGE->activityrecord;
$individualfeedbackstructure = new mod_individualfeedback_structure($individualfeedback, $cm);

$context = context_module::instance($cm->id);

if (!$individualfeedbackstructure->can_view_analysis()) {
    throw new \moodle_exception('error');
}

// Print the page header.

$PAGE->set_heading($course->fullname);
$PAGE->set_title($individualfeedback->name);
echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($individualfeedback->name));

// Print the tabs.
require('tabs.php');

// Print the sub tabs.
echo html_writer::start_tag('div', array('class' => 'subtabs_placeholder'));
require('tabs_evaluations.php');
echo html_writer::end_tag('div');

// Get the file based on the selected subtab.
if (!file_exists($CFG->dirroot . "/mod/individualfeedback/" . $currentsubtab . ".php")) {
    throw new \moodle_exception('error_subtab', 'individualfeedback');
} else {
    require($currentsubtab . ".php");
}

echo $OUTPUT->footer();
