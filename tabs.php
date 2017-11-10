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
 * prints the tabbed bar
 *
 * @author Andreas Grabs
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package mod_individualfeedback
 */
defined('MOODLE_INTERNAL') OR die('not allowed');

$tabs = array();
$row  = array();
$inactive = array();
$activated = array();

//some pages deliver the cmid instead the id
if (isset($cmid) AND intval($cmid) AND $cmid > 0) {
    $usedid = $cmid;
} else {
    $usedid = $id;
}

$context = context_module::instance($usedid);

$courseid = optional_param('courseid', false, PARAM_INT);
// $current_tab = $SESSION->individualfeedback->current_tab;
if (!isset($current_tab)) {
    $current_tab = '';
}

$viewurl = new moodle_url('/mod/individualfeedback/view.php', array('id' => $usedid));
$row[] = new tabobject('view', $viewurl->out(), get_string('overview', 'individualfeedback'));
$urlparams = ['id' => $usedid];
if ($individualfeedback->course == SITEID && $courseid) {
    $urlparams['courseid'] = $courseid;
}

if (has_capability('mod/individualfeedback:edititems', $context)) {
    $editurl = new moodle_url('/mod/individualfeedback/edit.php', $urlparams + ['do_show' => 'edit']);
    $row[] = new tabobject('edit', $editurl->out(), get_string('edit_items', 'individualfeedback'));

    $templateurl = new moodle_url('/mod/individualfeedback/edit.php', $urlparams + ['do_show' => 'templates']);
    $row[] = new tabobject('templates', $templateurl->out(), get_string('templates', 'individualfeedback'));
}

if ($individualfeedback->course == SITEID && has_capability('mod/individualfeedback:mapcourse', $context)) {
    $mapurl = new moodle_url('/mod/individualfeedback/mapcourse.php', $urlparams);
    $row[] = new tabobject('mapcourse', $mapurl->out(), get_string('mappedcourses', 'individualfeedback'));
}

if (has_capability('mod/individualfeedback:viewreports', $context)) {
    if ($individualfeedback->course == SITEID) {
        $analysisurl = new moodle_url('/mod/individualfeedback/analysis_course.php', $urlparams);
    } else {
        $analysisurl = new moodle_url('/mod/individualfeedback/analysis.php', $urlparams);
    }
    $row[] = new tabobject('analysis', $analysisurl->out(), get_string('evaluations', 'individualfeedback'));

    $reporturl = new moodle_url('/mod/individualfeedback/show_entries.php', $urlparams);
    $row[] = new tabobject('showentries',
                            $reporturl->out(),
                            get_string('show_entries', 'individualfeedback'));

    if ($individualfeedback->anonymous == INDIVIDUALFEEDBACK_ANONYMOUS_NO AND $individualfeedback->course != SITEID) {
        $nonrespondenturl = new moodle_url('/mod/individualfeedback/show_nonrespondents.php', $urlparams);
        $row[] = new tabobject('nonrespondents',
                                $nonrespondenturl->out(),
                                get_string('show_nonrespondents', 'individualfeedback'));
    }
}

if (count($row) > 1) {
    $tabs[] = $row;

    print_tabs($tabs, $current_tab, $inactive, $activated);
}

