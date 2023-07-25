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
 * Library of functions and constants for module individualfeedback
 * includes the main-part of individualfeedback-functions
 *
 * @package mod_individualfeedback
 * @copyright Andreas Grabs
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Include forms lib.
require_once($CFG->libdir.'/formslib.php');

define('INDIVIDUALFEEDBACK_ANONYMOUS_YES', 1);
define('INDIVIDUALFEEDBACK_ANONYMOUS_NO', 2);
define('INDIVIDUALFEEDBACK_MIN_ANONYMOUS_COUNT_IN_GROUP', 2);
define('INDIVIDUALFEEDBACK_DECIMAL', '.');
define('INDIVIDUALFEEDBACK_THOUSAND', ',');
define('INDIVIDUALFEEDBACK_RESETFORM_RESET', 'individualfeedback_reset_data_');
define('INDIVIDUALFEEDBACK_RESETFORM_DROP', 'individualfeedback_drop_individualfeedback_');
define('INDIVIDUALFEEDBACK_MAX_PIX_LENGTH', '400'); //max. Breite des grafischen Balkens in der Auswertung
define('INDIVIDUALFEEDBACK_DEFAULT_PAGE_COUNT', 20);

// Event types.
define('INDIVIDUALFEEDBACK_EVENT_TYPE_OPEN', 'open');
define('INDIVIDUALFEEDBACK_EVENT_TYPE_CLOSE', 'close');

/**
 * Returns all other caps used in module.
 *
 * @return array
 */
function individualfeedback_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}

/**
 * @uses FEATURE_GROUPS
 * @uses FEATURE_GROUPINGS
 * @uses FEATURE_MOD_INTRO
 * @uses FEATURE_COMPLETION_TRACKS_VIEWS
 * @uses FEATURE_GRADE_HAS_GRADE
 * @uses FEATURE_GRADE_OUTCOMES
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, null if doesn't know
 */
function individualfeedback_supports($feature) {
    switch($feature) {
        case FEATURE_GROUPS:                  return true;
        case FEATURE_GROUPINGS:               return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_COMPLETION_HAS_RULES:    return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}

/**
 * this will create a new instance and return the id number
 * of the new instance.
 *
 * @global object
 * @param object $individualfeedback the object given by mod_individualfeedback_mod_form
 * @return int
 */
function individualfeedback_add_instance($individualfeedback) {
    global $DB;

    $individualfeedback->timemodified = time();
    $individualfeedback->id = '';

    if (empty($individualfeedback->site_after_submit)) {
        $individualfeedback->site_after_submit = '';
    }

    //saving the individualfeedback in db
    $individualfeedbackid = $DB->insert_record("individualfeedback", $individualfeedback);

    $individualfeedback->id = $individualfeedbackid;

    individualfeedback_set_events($individualfeedback);

    if (!isset($individualfeedback->coursemodule)) {
        $cm = get_coursemodule_from_id('individualfeedback', $individualfeedback->id);
        $individualfeedback->coursemodule = $cm->id;
    }
    $context = context_module::instance($individualfeedback->coursemodule);

    if (!empty($individualfeedback->completionexpected)) {
        \core_completion\api::update_completion_date_event($individualfeedback->coursemodule, 'individualfeedback', $individualfeedback->id,
                $individualfeedback->completionexpected);
    }

    $editoroptions = individualfeedback_get_editor_options();

    // process the custom wysiwyg editor in page_after_submit
    if ($draftitemid = $individualfeedback->page_after_submit_editor['itemid']) {
        $individualfeedback->page_after_submit = file_save_draft_area_files($draftitemid, $context->id,
                                                    'mod_individualfeedback', 'page_after_submit',
                                                    0, $editoroptions,
                                                    $individualfeedback->page_after_submit_editor['text']);

        $individualfeedback->page_after_submitformat = $individualfeedback->page_after_submit_editor['format'];
    }
    $DB->update_record('individualfeedback', $individualfeedback);

    return $individualfeedbackid;
}

/**
 * this will update a given instance
 *
 * @global object
 * @param object $individualfeedback the object given by mod_individualfeedback_mod_form
 * @return boolean
 */
function individualfeedback_update_instance($individualfeedback) {
    global $DB;

    $individualfeedback->timemodified = time();
    $individualfeedback->id = $individualfeedback->instance;

    if (empty($individualfeedback->site_after_submit)) {
        $individualfeedback->site_after_submit = '';
    }

    //save the individualfeedback into the db
    $DB->update_record("individualfeedback", $individualfeedback);

    //create or update the new events
    individualfeedback_set_events($individualfeedback);
    $completionexpected = (!empty($individualfeedback->completionexpected)) ? $individualfeedback->completionexpected : null;
    \core_completion\api::update_completion_date_event($individualfeedback->coursemodule, 'individualfeedback', $individualfeedback->id, $completionexpected);

    $context = context_module::instance($individualfeedback->coursemodule);

    $editoroptions = individualfeedback_get_editor_options();

    // process the custom wysiwyg editor in page_after_submit
    if ($draftitemid = $individualfeedback->page_after_submit_editor['itemid']) {
        $individualfeedback->page_after_submit = file_save_draft_area_files($draftitemid, $context->id,
                                                    'mod_individualfeedback', 'page_after_submit',
                                                    0, $editoroptions,
                                                    $individualfeedback->page_after_submit_editor['text']);

        $individualfeedback->page_after_submitformat = $individualfeedback->page_after_submit_editor['format'];
    }
    $DB->update_record('individualfeedback', $individualfeedback);

    return true;
}

/**
 * Serves the files included in individualfeedback items like label. Implements needed access control ;-)
 *
 * There are two situations in general where the files will be sent.
 * 1) filearea = item, 2) filearea = template
 *
 * @package  mod_individualfeedback
 * @category files
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if file not found, does not return if found - justsend the file
 */
function individualfeedback_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $CFG, $DB;

    if ($filearea === 'item' or $filearea === 'template') {
        $itemid = (int)array_shift($args);
        //get the item what includes the file
        if (!$item = $DB->get_record('individualfeedback_item', array('id'=>$itemid))) {
            return false;
        }
        $individualfeedbackid = $item->individualfeedback;
        $templateid = $item->template;
    }

    if ($filearea === 'page_after_submit' or $filearea === 'item') {
        if (! $individualfeedback = $DB->get_record("individualfeedback", array("id"=>$cm->instance))) {
            return false;
        }

        $individualfeedbackid = $individualfeedback->id;

        //if the filearea is "item" so we check the permissions like view/complete the individualfeedback
        $canload = false;
        //first check whether the user has the complete capability
        if (has_capability('mod/individualfeedback:complete', $context)) {
            $canload = true;
        }

        //now we check whether the user has the view capability
        if (has_capability('mod/individualfeedback:view', $context)) {
            $canload = true;
        }

        //if the individualfeedback is on frontpage and anonymous and the fullanonymous is allowed
        //so the file can be loaded too.
        if (isset($CFG->individualfeedback_allowfullanonymous)
                    AND $CFG->individualfeedback_allowfullanonymous
                    AND $course->id == SITEID
                    AND $individualfeedback->anonymous == INDIVIDUALFEEDBACK_ANONYMOUS_YES ) {
            $canload = true;
        }

        if (!$canload) {
            return false;
        }
    } else if ($filearea === 'template') { //now we check files in templates
        if (!$template = $DB->get_record('individualfeedback_template', array('id'=>$templateid))) {
            return false;
        }

        //if the file is not public so the capability edititems has to be there
        if (!$template->ispublic) {
            if (!has_capability('mod/individualfeedback:edititems', $context)) {
                return false;
            }
        } else { //on public templates, at least the user has to be logged in
            if (!isloggedin()) {
                return false;
            }
        }
    } else {
        return false;
    }

    if ($context->contextlevel == CONTEXT_MODULE) {
        if ($filearea !== 'item' and $filearea !== 'page_after_submit') {
            return false;
        }
    }

    if ($context->contextlevel == CONTEXT_COURSE || $context->contextlevel == CONTEXT_SYSTEM) {
        if ($filearea !== 'template') {
            return false;
        }
    }

    $relativepath = implode('/', $args);
    if ($filearea === 'page_after_submit') {
        $fullpath = "/{$context->id}/mod_individualfeedback/$filearea/$relativepath";
    } else {
        $fullpath = "/{$context->id}/mod_individualfeedback/$filearea/{$item->id}/$relativepath";
    }

    $fs = get_file_storage();

    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

    // finally send the file
    send_stored_file($file, 0, 0, true, $options); // download MUST be forced - security!

    return false;
}

/**
 * this will delete a given instance.
 * all referenced data also will be deleted
 *
 * @global object
 * @param int $id the instanceid of individualfeedback
 * @return boolean
 */
function individualfeedback_delete_instance($id) {
    global $DB;

    //get all referenced items
    $individualfeedbackitems = $DB->get_records('individualfeedback_item', array('individualfeedback'=>$id));

    //deleting all referenced items and values
    if (is_array($individualfeedbackitems)) {
        foreach ($individualfeedbackitems as $individualfeedbackitem) {
            $DB->delete_records("individualfeedback_value", array("item"=>$individualfeedbackitem->id));
            $DB->delete_records("individualfeedback_valuetmp", array("item"=>$individualfeedbackitem->id));
        }
        if ($delitems = $DB->get_records("individualfeedback_item", array("individualfeedback"=>$id))) {
            foreach ($delitems as $delitem) {
                individualfeedback_delete_item($delitem->id, false);
            }
        }
    }

    //deleting the completeds
    $DB->delete_records("individualfeedback_completed", array("individualfeedback"=>$id));

    //deleting the unfinished completeds
    $DB->delete_records("indfeedback_completedtmp", array("individualfeedback"=>$id));

    // Delete the activity from the linked table.
    if ($linkedid = individualfeedback_get_linkedid($id)) {
        $DB->delete_records('individualfeedback_linked', array('individualfeedbackid' => $id));
        // If there is only 1 record left, delete that record as well.
        if ($DB->count_records('individualfeedback_linked', array('linkedid' => $linkedid)) < 2) {
            $DB->delete_records('individualfeedback_linked',  array('linkedid' => $linkedid));
        }
    }

    //deleting old events
    $DB->delete_records('event', array('modulename'=>'individualfeedback', 'instance'=>$id));
    return $DB->delete_records("individualfeedback", array("id"=>$id));
}

/**
 * Return a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @param stdClass $course
 * @param stdClass $user
 * @param cm_info|stdClass $mod
 * @param stdClass $individualfeedback
 * @return stdClass
 */
function individualfeedback_user_outline($course, $user, $mod, $individualfeedback) {
    global $DB;
    $outline = (object)['info' => '', 'time' => 0];
    if ($individualfeedback->anonymous != INDIVIDUALFEEDBACK_ANONYMOUS_NO) {
        // Do not disclose any user info if individualfeedback is anonymous.
        return $outline;
    }
    $params = array('userid' => individualfeedback_hash_userid($user->id), 'individualfeedback' => $individualfeedback->id,
        'anonymous_response' => INDIVIDUALFEEDBACK_ANONYMOUS_NO);
    $status = null;
    $context = context_module::instance($mod->id);
    if ($completed = $DB->get_record('individualfeedback_completed', $params)) {
        // User has completed individualfeedback.
        $outline->info = get_string('completed', 'individualfeedback');
        $outline->time = $completed->timemodified;
    } else if ($completedtmp = $DB->get_record('indfeedback_completedtmp', $params)) {
        // User has started but not completed individualfeedback.
        $outline->info = get_string('started', 'individualfeedback');
        $outline->time = $completedtmp->timemodified;
    } else if (has_capability('mod/individualfeedback:complete', $context, $user)) {
        // User has not started individualfeedback but has capability to do so.
        $outline->info = get_string('not_started', 'individualfeedback');
    }

    return $outline;
}

/**
 * Returns all users who has completed a specified individualfeedback since a given time
 * many thanks to Manolescu Dorel, who contributed these two functions
 *
 * @global object
 * @global object
 * @global object
 * @global object
 * @uses CONTEXT_MODULE
 * @param array $activities Passed by reference
 * @param int $index Passed by reference
 * @param int $timemodified Timestamp
 * @param int $courseid
 * @param int $cmid
 * @param int $userid
 * @param int $groupid
 * @return void
 */
function individualfeedback_get_recent_mod_activity(&$activities, &$index,
                                          $timemodified, $courseid,
                                          $cmid, $userid="", $groupid="") {

    global $CFG, $COURSE, $USER, $DB;

    if ($COURSE->id == $courseid) {
        $course = $COURSE;
    } else {
        $course = $DB->get_record('course', array('id'=>$courseid));
    }

    $modinfo = get_fast_modinfo($course);

    $cm = $modinfo->cms[$cmid];

    $sqlargs = array();

    $userfields = user_picture::fields('u', null, 'useridagain');
    $sql = " SELECT fk . * , fc . * , $userfields
                FROM {individualfeedback_completed} fc
                    JOIN {individualfeedback} fk ON fk.id = fc.individualfeedback
                    JOIN {user} u ON u.id = fc.userid ";

    if ($groupid) {
        $sql .= " JOIN {groups_members} gm ON  gm.userid=u.id ";
    }

    $sql .= " WHERE fc.timemodified > ?
                AND fk.id = ?
                AND fc.anonymous_response = ?";
    $sqlargs[] = $timemodified;
    $sqlargs[] = $cm->instance;
    $sqlargs[] = INDIVIDUALFEEDBACK_ANONYMOUS_NO;

    if ($userid) {
        $sql .= " AND u.id = ? ";
        $sqlargs[] = $userid;
    }

    if ($groupid) {
        $sql .= " AND gm.groupid = ? ";
        $sqlargs[] = $groupid;
    }

    if (!$individualfeedbackitems = $DB->get_records_sql($sql, $sqlargs)) {
        return;
    }

    $cm_context = context_module::instance($cm->id);

    if (!has_capability('mod/individualfeedback:view', $cm_context)) {
        return;
    }

    $accessallgroups = has_capability('moodle/site:accessallgroups', $cm_context);
    $viewfullnames   = has_capability('moodle/site:viewfullnames', $cm_context);
    $groupmode       = groups_get_activity_groupmode($cm, $course);

    $aname = format_string($cm->name, true);
    foreach ($individualfeedbackitems as $individualfeedbackitem) {
        if ($individualfeedbackitem->userid != $USER->id) {

            if ($groupmode == SEPARATEGROUPS and !$accessallgroups) {
                $usersgroups = groups_get_all_groups($course->id,
                                                     $individualfeedbackitem->userid,
                                                     $cm->groupingid);
                if (!is_array($usersgroups)) {
                    continue;
                }
                $usersgroups = array_keys($usersgroups);
                $intersect = array_intersect($usersgroups, $modinfo->get_groups($cm->groupingid));
                if (empty($intersect)) {
                    continue;
                }
            }
        }

        $tmpactivity = new stdClass();

        $tmpactivity->type      = 'individualfeedback';
        $tmpactivity->cmid      = $cm->id;
        $tmpactivity->name      = $aname;
        $tmpactivity->sectionnum= $cm->sectionnum;
        $tmpactivity->timestamp = $individualfeedbackitem->timemodified;

        $tmpactivity->content = new stdClass();
        $tmpactivity->content->individualfeedbackid = $individualfeedbackitem->id;
        $tmpactivity->content->individualfeedbackuserid = $individualfeedbackitem->userid;

        $tmpactivity->user = user_picture::unalias($individualfeedbackitem, null, 'useridagain');
        $tmpactivity->user->fullname = fullname($individualfeedbackitem, $viewfullnames);

        $activities[$index++] = $tmpactivity;
    }

    return;
}

/**
 * Prints all users who has completed a specified individualfeedback since a given time
 * many thanks to Manolescu Dorel, who contributed these two functions
 *
 * @global object
 * @param object $activity
 * @param int $courseid
 * @param string $detail
 * @param array $modnames
 * @return void Output is echo'd
 */
function individualfeedback_print_recent_mod_activity($activity, $courseid, $detail, $modnames) {
    global $CFG, $OUTPUT;

    echo '<table border="0" cellpadding="3" cellspacing="0" class="forum-recent">';

    echo "<tr><td class=\"userpicture\" valign=\"top\">";
    echo $OUTPUT->user_picture($activity->user, array('courseid'=>$courseid));
    echo "</td><td>";

    if ($detail) {
        $modname = $modnames[$activity->type];
        echo '<div class="title">';
        echo $OUTPUT->image_icon('icon', $modname, $activity->type);
        echo "<a href=\"$CFG->wwwroot/mod/individualfeedback/view.php?id={$activity->cmid}\">{$activity->name}</a>";
        echo '</div>';
    }

    echo '<div class="title">';
    echo '</div>';

    echo '<div class="user">';
    echo "<a href=\"$CFG->wwwroot/user/view.php?id={$activity->user->id}&amp;course=$courseid\">"
         ."{$activity->user->fullname}</a> - ".userdate($activity->timestamp);
    echo '</div>';

    echo "</td></tr></table>";

    return;
}

/**
 * Obtains the automatic completion state for this individualfeedback based on the condition
 * in individualfeedback settings.
 *
 * @param object $course Course
 * @param object $cm Course-module
 * @param int $userid User ID
 * @param bool $type Type of comparison (or/and; can be used as return value if no conditions)
 * @return bool True if completed, false if not, $type if conditions not set.
 */
function individualfeedback_get_completion_state($course, $cm, $userid, $type) {
    global $CFG, $DB;

    // Get individualfeedback details
    $individualfeedback = $DB->get_record('individualfeedback', array('id'=>$cm->instance), '*', MUST_EXIST);

    // If completion option is enabled, evaluate it and return true/false
    if ($individualfeedback->completionsubmit) {
        $params = array('userid'=>individualfeedback_hash_userid($userid), 'individualfeedback'=>$individualfeedback->id);
        return $DB->record_exists('individualfeedback_completed', $params);
    } else {
        // Completion option is not enabled so just return $type
        return $type;
    }
}

/**
 * Print a detailed representation of what a  user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param stdClass $course
 * @param stdClass $user
 * @param cm_info|stdClass $mod
 * @param stdClass $individualfeedback
 */
function individualfeedback_user_complete($course, $user, $mod, $individualfeedback) {
    global $DB;
    if ($individualfeedback->anonymous != INDIVIDUALFEEDBACK_ANONYMOUS_NO) {
        // Do not disclose any user info if individualfeedback is anonymous.
        return;
    }
    $params = array('userid' => individualfeedback_hash_userid($user->id), 'individualfeedback' => $individualfeedback->id,
        'anonymous_response' => INDIVIDUALFEEDBACK_ANONYMOUS_NO);
    $url = $status = null;
    $context = context_module::instance($mod->id);
    if ($completed = $DB->get_record('individualfeedback_completed', $params)) {
        // User has completed individualfeedback.
        if (has_capability('mod/individualfeedback:viewreports', $context)) {
            $url = new moodle_url('/mod/individualfeedback/show_entries.php',
                ['id' => $mod->id, 'userid' => $user->id,
                    'showcompleted' => $completed->id]);
        }
        $status = get_string('completedon', 'individualfeedback', userdate($completed->timemodified));
    } else if ($completedtmp = $DB->get_record('indfeedback_completedtmp', $params)) {
        // User has started but not completed individualfeedback.
        $status = get_string('startedon', 'individualfeedback', userdate($completedtmp->timemodified));
    } else if (has_capability('mod/individualfeedback:complete', $context, $user)) {
        // User has not started individualfeedback but has capability to do so.
        $status = get_string('not_started', 'individualfeedback');
    }

    if ($url && $status) {
        echo html_writer::link($url, $status);
    } else if ($status) {
        echo html_writer::div($status);
    }
}

/**
 * @return bool true
 */
function individualfeedback_cron () {
    return true;
}

/**
 * @return bool false
 */
function individualfeedback_scale_used ($individualfeedbackid, $scaleid) {
    return false;
}

/**
 * Checks if scale is being used by any instance of individualfeedback
 *
 * This is used to find out if scale used anywhere
 * @param $scaleid int
 * @return boolean True if the scale is used by any assignment
 */
function individualfeedback_scale_used_anywhere($scaleid) {
    return false;
}

/**
 * List the actions that correspond to a view of this module.
 * This is used by the participation report.
 *
 * Note: This is not used by new logging system. Event with
 *       crud = 'r' and edulevel = LEVEL_PARTICIPATING will
 *       be considered as view action.
 *
 * @return array
 */
function individualfeedback_get_view_actions() {
    return array('view', 'view all');
}

/**
 * List the actions that correspond to a post of this module.
 * This is used by the participation report.
 *
 * Note: This is not used by new logging system. Event with
 *       crud = ('c' || 'u' || 'd') and edulevel = LEVEL_PARTICIPATING
 *       will be considered as post action.
 *
 * @return array
 */
function individualfeedback_get_post_actions() {
    return array('submit');
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * This function will remove all responses from the specified individualfeedback
 * and clean up any related data.
 *
 * @global object
 * @global object
 * @uses INDIVIDUALFEEDBACK_RESETFORM_RESET
 * @uses INDIVIDUALFEEDBACK_RESETFORM_DROP
 * @param object $data the data submitted from the reset course.
 * @return array status array
 */
function individualfeedback_reset_userdata($data) {
    global $CFG, $DB;

    $resetindividualfeedbacks = array();
    $dropindividualfeedbacks = array();
    $status = array();
    $componentstr = get_string('modulenameplural', 'individualfeedback');

    //get the relevant entries from $data
    foreach ($data as $key => $value) {
        switch(true) {
            case substr($key, 0, strlen(INDIVIDUALFEEDBACK_RESETFORM_RESET)) == INDIVIDUALFEEDBACK_RESETFORM_RESET:
                if ($value == 1) {
                    $templist = explode('_', $key);
                    if (isset($templist[3])) {
                        $resetindividualfeedbacks[] = intval($templist[3]);
                    }
                }
            break;
            case substr($key, 0, strlen(INDIVIDUALFEEDBACK_RESETFORM_DROP)) == INDIVIDUALFEEDBACK_RESETFORM_DROP:
                if ($value == 1) {
                    $templist = explode('_', $key);
                    if (isset($templist[3])) {
                        $dropindividualfeedbacks[] = intval($templist[3]);
                    }
                }
            break;
        }
    }

    //reset the selected individualfeedbacks
    foreach ($resetindividualfeedbacks as $id) {
        $individualfeedback = $DB->get_record('individualfeedback', array('id'=>$id));
        individualfeedback_delete_all_completeds($individualfeedback);
        $status[] = array('component'=>$componentstr.':'.$individualfeedback->name,
                        'item'=>get_string('resetting_data', 'individualfeedback'),
                        'error'=>false);
    }

    // Updating dates - shift may be negative too.
    if ($data->timeshift) {
        $shifterror = !shift_course_mod_dates('individualfeedback', array('timeopen', 'timeclose'), $data->timeshift, $data->courseid);
        $status[] = array('component' => $componentstr, 'item' => get_string('datechanged'), 'error' => $shifterror);
    }

    return $status;
}

/**
 * Called by course/reset.php
 *
 * @global object
 * @uses INDIVIDUALFEEDBACK_RESETFORM_RESET
 * @param object $mform form passed by reference
 */
function individualfeedback_reset_course_form_definition(&$mform) {
    global $COURSE, $DB;

    $mform->addElement('header', 'individualfeedbackheader', get_string('modulenameplural', 'individualfeedback'));

    if (!$individualfeedbacks = $DB->get_records('individualfeedback', array('course'=>$COURSE->id), 'name')) {
        return;
    }

    $mform->addElement('static', 'hint', get_string('resetting_data', 'individualfeedback'));
    foreach ($individualfeedbacks as $individualfeedback) {
        $mform->addElement('checkbox', INDIVIDUALFEEDBACK_RESETFORM_RESET.$individualfeedback->id, $individualfeedback->name);
    }
}

/**
 * Course reset form defaults.
 *
 * @global object
 * @uses INDIVIDUALFEEDBACK_RESETFORM_RESET
 * @param object $course
 */
function individualfeedback_reset_course_form_defaults($course) {
    global $DB;

    $return = array();
    if (!$individualfeedbacks = $DB->get_records('individualfeedback', array('course'=>$course->id), 'name')) {
        return;
    }
    foreach ($individualfeedbacks as $individualfeedback) {
        $return[INDIVIDUALFEEDBACK_RESETFORM_RESET.$individualfeedback->id] = true;
    }
    return $return;
}

/**
 * Called by course/reset.php and shows the formdata by coursereset.
 * it prints checkboxes for each individualfeedback available at the given course
 * there are two checkboxes:
 * 1) delete userdata and keep the individualfeedback
 * 2) delete userdata and drop the individualfeedback
 *
 * @global object
 * @uses INDIVIDUALFEEDBACK_RESETFORM_RESET
 * @uses INDIVIDUALFEEDBACK_RESETFORM_DROP
 * @param object $course
 * @return void
 */
function individualfeedback_reset_course_form($course) {
    global $DB, $OUTPUT;

    echo get_string('resetting_individualfeedbacks', 'individualfeedback'); echo ':<br />';
    if (!$individualfeedbacks = $DB->get_records('individualfeedback', array('course'=>$course->id), 'name')) {
        return;
    }

    foreach ($individualfeedbacks as $individualfeedback) {
        echo '<p>';
        echo get_string('name', 'individualfeedback').': '.$individualfeedback->name.'<br />';
        echo html_writer::checkbox(INDIVIDUALFEEDBACK_RESETFORM_RESET.$individualfeedback->id,
                                1, true,
                                get_string('resetting_data', 'individualfeedback'));
        echo '<br />';
        echo html_writer::checkbox(INDIVIDUALFEEDBACK_RESETFORM_DROP.$individualfeedback->id,
                                1, false,
                                get_string('drop_individualfeedback', 'individualfeedback'));
        echo '</p>';
    }
}

/**
 * This gets an array with default options for the editor
 *
 * @return array the options
 */
function individualfeedback_get_editor_options() {
    return array('maxfiles' => EDITOR_UNLIMITED_FILES,
                'trusttext'=>true);
}

/**
 * This creates new events given as timeopen and closeopen by $individualfeedback.
 *
 * @global object
 * @param object $individualfeedback
 * @return void
 */
function individualfeedback_set_events($individualfeedback) {
    global $DB, $CFG;

    // Include calendar/lib.php.
    require_once($CFG->dirroot.'/calendar/lib.php');

    // Get CMID if not sent as part of $individualfeedback.
    if (!isset($individualfeedback->coursemodule)) {
        $cm = get_coursemodule_from_instance('individualfeedback', $individualfeedback->id, $individualfeedback->course);
        $individualfeedback->coursemodule = $cm->id;
    }

    // individualfeedback start calendar events.
    $eventid = $DB->get_field('event', 'id',
            array('modulename' => 'individualfeedback', 'instance' => $individualfeedback->id, 'eventtype' => INDIVIDUALFEEDBACK_EVENT_TYPE_OPEN));

    if (isset($individualfeedback->timeopen) && $individualfeedback->timeopen > 0) {
        $event = new stdClass();
        $event->eventtype    = INDIVIDUALFEEDBACK_EVENT_TYPE_OPEN;
        $event->type         = empty($individualfeedback->timeclose) ? CALENDAR_EVENT_TYPE_ACTION : CALENDAR_EVENT_TYPE_STANDARD;
        $event->name         = get_string('calendarstart', 'individualfeedback', $individualfeedback->name);
        $event->description  = format_module_intro('individualfeedback', $individualfeedback, $individualfeedback->coursemodule, false);
        $event->format       = FORMAT_HTML;
        $event->timestart    = $individualfeedback->timeopen;
        $event->timesort     = $individualfeedback->timeopen;
        $event->visible      = instance_is_visible('individualfeedback', $individualfeedback);
        $event->timeduration = 0;
        if ($eventid) {
            // Calendar event exists so update it.
            $event->id = $eventid;
            $calendarevent = calendar_event::load($event->id);
            $calendarevent->update($event);
        } else {
            // Event doesn't exist so create one.
            $event->courseid     = $individualfeedback->course;
            $event->groupid      = 0;
            $event->userid       = 0;
            $event->modulename   = 'individualfeedback';
            $event->instance     = $individualfeedback->id;
            $event->eventtype    = INDIVIDUALFEEDBACK_EVENT_TYPE_OPEN;
            calendar_event::create($event);
        }
    } else if ($eventid) {
        // Calendar event is on longer needed.
        $calendarevent = calendar_event::load($eventid);
        $calendarevent->delete();
    }

    // individualfeedback close calendar events.
    $eventid = $DB->get_field('event', 'id',
            array('modulename' => 'individualfeedback', 'instance' => $individualfeedback->id, 'eventtype' => INDIVIDUALFEEDBACK_EVENT_TYPE_CLOSE));

    if (isset($individualfeedback->timeclose) && $individualfeedback->timeclose > 0) {
        $event = new stdClass();
        $event->type         = CALENDAR_EVENT_TYPE_ACTION;
        $event->eventtype    = INDIVIDUALFEEDBACK_EVENT_TYPE_CLOSE;
        $event->name         = get_string('calendarend', 'individualfeedback', $individualfeedback->name);
        $event->description  = format_module_intro('individualfeedback', $individualfeedback, $individualfeedback->coursemodule, false);
        $event->format       = FORMAT_HTML;
        $event->timestart    = $individualfeedback->timeclose;
        $event->timesort     = $individualfeedback->timeclose;
        $event->visible      = instance_is_visible('individualfeedback', $individualfeedback);
        $event->timeduration = 0;
        if ($eventid) {
            // Calendar event exists so update it.
            $event->id = $eventid;
            $calendarevent = calendar_event::load($event->id);
            $calendarevent->update($event);
        } else {
            // Event doesn't exist so create one.
            $event->courseid     = $individualfeedback->course;
            $event->groupid      = 0;
            $event->userid       = 0;
            $event->modulename   = 'individualfeedback';
            $event->instance     = $individualfeedback->id;
            calendar_event::create($event);
        }
    } else if ($eventid) {
        // Calendar event is on longer needed.
        $calendarevent = calendar_event::load($eventid);
        $calendarevent->delete();
    }
}

/**
 * This standard function will check all instances of this module
 * and make sure there are up-to-date events created for each of them.
 * If courseid = 0, then every individualfeedback event in the site is checked, else
 * only individualfeedback events belonging to the course specified are checked.
 * This function is used, in its new format, by restore_refresh_events()
 *
 * @param int $courseid
 * @param int|stdClass $instance individualfeedback module instance or ID.
 * @param int|stdClass $cm Course module object or ID (not used in this module).
 * @return bool
 */
function individualfeedback_refresh_events($courseid = 0, $instance = null, $cm = null) {
    global $DB;

    // If we have instance information then we can just update the one event instead of updating all events.
    if (isset($instance)) {
        if (!is_object($instance)) {
            $instance = $DB->get_record('individualfeedback', array('id' => $instance), '*', MUST_EXIST);
        }
        individualfeedback_set_events($instance);
        return true;
    }

    if ($courseid) {
        if (! $individualfeedbacks = $DB->get_records("individualfeedback", array("course" => $courseid))) {
            return true;
        }
    } else {
        if (! $individualfeedbacks = $DB->get_records("individualfeedback")) {
            return true;
        }
    }

    foreach ($individualfeedbacks as $individualfeedback) {
        individualfeedback_set_events($individualfeedback);
    }
    return true;
}

/**
 * this function is called by {@link individualfeedback_delete_userdata()}
 * it drops the individualfeedback-instance from the course_module table
 *
 * @global object
 * @param int $id the id from the coursemodule
 * @return boolean
 */
function individualfeedback_delete_course_module($id) {
    global $DB;

    if (!$cm = $DB->get_record('course_modules', array('id'=>$id))) {
        return true;
    }
    return $DB->delete_records('course_modules', array('id'=>$cm->id));
}



////////////////////////////////////////////////
//functions to handle capabilities
////////////////////////////////////////////////

/**
 * returns the context-id related to the given coursemodule-id
 *
 * @deprecated since 3.1
 * @staticvar object $context
 * @param int $cmid the coursemodule-id
 * @return object $context
 */
function individualfeedback_get_context($cmid) {
    debugging('Function individualfeedback_get_context() is deprecated because it was not used.',
            DEBUG_DEVELOPER);
    static $context;

    if (isset($context)) {
        return $context;
    }

    $context = context_module::instance($cmid);
    return $context;
}

/**
 *  returns true if the current role is faked by switching role feature
 *
 * @param int courseid - the id of the course
 * @return boolean
 */
function individualfeedback_check_is_switchrole($courseid) {
    return is_role_switched($courseid);
}

/**
 *  Returns true if the current users is logged in as someone else.
 *
 * @global object
 * @return boolean
 */
function individualfeedback_check_is_loggedinas() {
    global $USER;
    if (isset($USER->realuser) && $USER->realuser != $USER->id) {
        return true;
    }

    return false;
}

/**
 * count users which have not completed the individualfeedback
 *
 * @global object
 * @uses CONTEXT_MODULE
 * @param cm_info $cm Course-module object
 * @param int $group single groupid
 * @param string $sort
 * @param int $startpage
 * @param int $pagecount
 * @param bool $includestatus to return if the user started or not the individualfeedback among the complete user record
 * @return array array of user ids or user objects when $includestatus set to true
 */
function individualfeedback_get_incomplete_users(cm_info $cm,
                                       $group = false,
                                       $sort = '',
                                       $startpage = false,
                                       $pagecount = false,
                                       $includestatus = false) {

    global $DB;

    $context = context_module::instance($cm->id);

    //first get all user who can complete this individualfeedback
    $cap = 'mod/individualfeedback:complete';
    $userfieldsapi = \core_user\fields::for_name();
    $allnames = $userfieldsapi->get_sql('u', false, '', '', false)->selects;
    $fields = 'u.id, ' . $allnames . ', u.picture, u.email, u.imagealt';
    if (!$allusers = get_users_by_capability($context,
                                            $cap,
                                            $fields,
                                            $sort,
                                            '',
                                            '',
                                            $group,
                                            '',
                                            true)) {
        return false;
    }
    // Filter users that are not in the correct group/grouping.
    $info = new \core_availability\info_module($cm);
    $allusersrecords = $info->filter_user_list($allusers);

    $allusers = array_keys($allusersrecords);

    //now get all completeds
    $params = array('individualfeedback'=>$cm->instance);
    if ($completedusers = $DB->get_records_menu('individualfeedback_completed', $params, '', 'id, userid')) {

        // Completed users are stored with hashes like userid = asdf893hrt4w98ergzw38934.
        $allusershashes = [];
        foreach ($allusers as $userid) {
            $allusershashes[$userid] = individualfeedback_hash_userid($userid);
        }
        // Now strike all completedusers from allusers.
        $noncompletehashes = array_diff($allusershashes, $completedusers);
        $allusers = array_keys($noncompletehashes);
    }

    //for paging I use array_slice()
    if ($startpage !== false AND $pagecount !== false) {
        $allusers = array_slice($allusers, $startpage, $pagecount);
    }

    // Check if we should return the full users objects.
    if ($includestatus) {
        $userrecords = [];
        $startedusers = $DB->get_records_menu('indfeedback_completedtmp', ['individualfeedback' => $cm->instance], '', 'id, userid');
        $startedusers = array_flip($startedusers);
        foreach ($allusers as $userid) {
            $allusersrecords[$userid]->individualfeedbackstarted = isset($startedusers[$userid]);
            $userrecords[] = $allusersrecords[$userid];
        }
        return $userrecords;
    } else {    // Return just user ids.
        return $allusers;
    }
}

/**
 * count users which have not completed the individualfeedback
 *
 * @global object
 * @param object $cm
 * @param int $group single groupid
 * @return int count of userrecords
 */
function individualfeedback_count_incomplete_users($cm, $group = false) {
    if ($allusers = individualfeedback_get_incomplete_users($cm, $group)) {
        return count($allusers);
    }
    return 0;
}

/**
 * count users which have completed a individualfeedback
 *
 * @global object
 * @uses INDIVIDUALFEEDBACK_ANONYMOUS_NO
 * @param object $cm
 * @param int $group single groupid
 * @return int count of userrecords
 */
function individualfeedback_count_complete_users($cm, $group = false) {
    global $DB;

    $params = array(INDIVIDUALFEEDBACK_ANONYMOUS_NO, $cm->instance);

    $fromgroup = '';
    $wheregroup = '';
    if ($group) {
        $fromgroup = ', {groups_members} g';
        $wheregroup = ' AND g.groupid = ? AND g.userid = c.userid';
        $params[] = $group;
    }

    $sql = 'SELECT COUNT(u.id) FROM {user} u, {individualfeedback_completed} c'.$fromgroup.'
              WHERE anonymous_response = ? AND u.id = c.userid AND c.individualfeedback = ?
              '.$wheregroup;

    return $DB->count_records_sql($sql, $params);

}

/**
 * get users which have completed a individualfeedback
 *
 * @global object
 * @uses CONTEXT_MODULE
 * @uses INDIVIDUALFEEDBACK_ANONYMOUS_NO
 * @param object $cm
 * @param int $group single groupid
 * @param string $where a sql where condition (must end with " AND ")
 * @param array parameters used in $where
 * @param string $sort a table field
 * @param int $startpage
 * @param int $pagecount
 * @return object the userrecords
 */
function individualfeedback_get_complete_users($cm,
                                     $group = false,
                                     $where = '',
                                     array $params = null,
                                     $sort = '',
                                     $startpage = false,
                                     $pagecount = false) {

    global $DB;

    $context = context_module::instance($cm->id);

    $params = (array)$params;

    $params['anon'] = INDIVIDUALFEEDBACK_ANONYMOUS_NO;
    $params['instance'] = $cm->instance;

    $fromgroup = '';
    $wheregroup = '';
    if ($group) {
        $fromgroup = ', {groups_members} g';
        $wheregroup = ' AND g.groupid = :group AND g.userid = c.userid';
        $params['group'] = $group;
    }

    if ($sort) {
        $sortsql = ' ORDER BY '.$sort;
    } else {
        $sortsql = '';
    }

    $ufields = user_picture::fields('u');
    $sql = 'SELECT DISTINCT '.$ufields.', c.timemodified as completed_timemodified
            FROM {user} u, {individualfeedback_completed} c '.$fromgroup.'
            WHERE '.$where.' anonymous_response = :anon
                AND u.id = c.userid
                AND c.individualfeedback = :instance
              '.$wheregroup.$sortsql;

    if ($startpage === false OR $pagecount === false) {
        $startpage = false;
        $pagecount = false;
    }
    return $DB->get_records_sql($sql, $params, $startpage, $pagecount);
}

/**
 * get users which have the viewreports-capability
 *
 * @uses CONTEXT_MODULE
 * @param int $cmid
 * @param mixed $groups single groupid or array of groupids - group(s) user is in
 * @return object the userrecords
 */
function individualfeedback_get_viewreports_users($cmid, $groups = false) {

    $context = context_module::instance($cmid);

    //description of the call below:
    //get_users_by_capability($context, $capability, $fields='', $sort='', $limitfrom='',
    //                          $limitnum='', $groups='', $exceptions='', $doanything=true)
    return get_users_by_capability($context,
                            'mod/individualfeedback:viewreports',
                            '',
                            'lastname',
                            '',
                            '',
                            $groups,
                            '',
                            false);
}

/**
 * get users which have the receivemail-capability
 *
 * @uses CONTEXT_MODULE
 * @param int $cmid
 * @param mixed $groups single groupid or array of groupids - group(s) user is in
 * @return object the userrecords
 */
function individualfeedback_get_receivemail_users($cmid, $groups = false) {

    $context = context_module::instance($cmid);

    //description of the call below:
    //get_users_by_capability($context, $capability, $fields='', $sort='', $limitfrom='',
    //                          $limitnum='', $groups='', $exceptions='', $doanything=true)
    return get_users_by_capability($context,
                            'mod/individualfeedback:receivemail',
                            '',
                            'lastname',
                            '',
                            '',
                            $groups,
                            '',
                            false);
}

////////////////////////////////////////////////
//functions to handle the templates
////////////////////////////////////////////////
////////////////////////////////////////////////

/**
 * creates a new template-record.
 *
 * @global object
 * @param int $courseid
 * @param string $name the name of template shown in the templatelist
 * @param int $ispublic 0:privat 1:public
 * @return int the new templateid
 */
function individualfeedback_create_template($courseid, $name, $ispublic = 0) {
    global $DB;

    $templ = new stdClass();
    $templ->course   = ($ispublic ? 0 : $courseid);
    $templ->name     = $name;
    $templ->ispublic = $ispublic;

    $templid = $DB->insert_record('individualfeedback_template', $templ);
    return $DB->get_record('individualfeedback_template', array('id'=>$templid));
}

/**
 * creates new template items.
 * all items will be copied and the attribute individualfeedback will be set to 0
 * and the attribute template will be set to the new templateid
 *
 * @global object
 * @uses CONTEXT_MODULE
 * @uses CONTEXT_COURSE
 * @param object $individualfeedback
 * @param string $name the name of template shown in the templatelist
 * @param int $ispublic 0:privat 1:public
 * @return boolean
 */
function individualfeedback_save_as_template($individualfeedback, $name, $ispublic = 0) {
    global $DB;
    $fs = get_file_storage();

    if (!$individualfeedbackitems = $DB->get_records('individualfeedback_item', array('individualfeedback'=>$individualfeedback->id))) {
        return false;
    }

    if (!$newtempl = individualfeedback_create_template($individualfeedback->course, $name, $ispublic)) {
        return false;
    }

    //files in the template_item are in the context of the current course or
    //if the template is public the files are in the system context
    //files in the individualfeedback_item are in the individualfeedback_context of the individualfeedback
    if ($ispublic) {
        $s_context = context_system::instance();
    } else {
        $s_context = context_course::instance($newtempl->course);
    }
    $cm = get_coursemodule_from_instance('individualfeedback', $individualfeedback->id);
    $f_context = context_module::instance($cm->id);

    //create items of this new template
    //depend items we are storing temporary in an mapping list array(new id => dependitem)
    //we also store a mapping of all items array(oldid => newid)
    $dependitemsmap = array();
    $itembackup = array();
    foreach ($individualfeedbackitems as $item) {

        $t_item = clone($item);

        unset($t_item->id);
        $t_item->individualfeedback = 0;
        $t_item->template     = $newtempl->id;
        $t_item->id = $DB->insert_record('individualfeedback_item', $t_item);
        //copy all included files to the individualfeedback_template filearea
        $itemfiles = $fs->get_area_files($f_context->id,
                                    'mod_individualfeedback',
                                    'item',
                                    $item->id,
                                    "id",
                                    false);
        if ($itemfiles) {
            foreach ($itemfiles as $ifile) {
                $file_record = new stdClass();
                $file_record->contextid = $s_context->id;
                $file_record->component = 'mod_individualfeedback';
                $file_record->filearea = 'template';
                $file_record->itemid = $t_item->id;
                $fs->create_file_from_storedfile($file_record, $ifile);
            }
        }

        $itembackup[$item->id] = $t_item->id;
        if ($t_item->dependitem) {
            $dependitemsmap[$t_item->id] = $t_item->dependitem;
        }

    }

    //remapping the dependency
    foreach ($dependitemsmap as $key => $dependitem) {
        $newitem = $DB->get_record('individualfeedback_item', array('id'=>$key));
        $newitem->dependitem = $itembackup[$newitem->dependitem];
        $DB->update_record('individualfeedback_item', $newitem);
    }

    return true;
}

/**
 * deletes all individualfeedback_items related to the given template id
 *
 * @global object
 * @uses CONTEXT_COURSE
 * @param object $template the template
 * @return void
 */
function individualfeedback_delete_template($template) {
    global $DB;

    //deleting the files from the item is done by individualfeedback_delete_item
    if ($t_items = $DB->get_records("individualfeedback_item", array("template"=>$template->id))) {
        foreach ($t_items as $t_item) {
            individualfeedback_delete_item($t_item->id, false, $template);
        }
    }
    $DB->delete_records("individualfeedback_template", array("id"=>$template->id));
}

/**
 * creates new individualfeedback_item-records from template.
 * if $deleteold is set true so the existing items of the given individualfeedback will be deleted
 * if $deleteold is set false so the new items will be appanded to the old items
 *
 * @global object
 * @uses CONTEXT_COURSE
 * @uses CONTEXT_MODULE
 * @param object $individualfeedback
 * @param int $templateid
 * @param object $formdata - the posted formdata
 */
function individualfeedback_items_from_template($individualfeedback, $templateid, $formdata) {
    global $DB, $CFG;

    require_once($CFG->libdir.'/completionlib.php');

    $fs = get_file_storage();

    if (!$template = $DB->get_record('individualfeedback_template', array('id'=>$templateid))) {
        return false;
    }
    //get all templateitems
    if (!$templitems = $DB->get_records('individualfeedback_item', array('template'=>$templateid))) {
        return false;
    }

    //files in the template_item are in the context of the current course
    //files in the individualfeedback_item are in the individualfeedback_context of the individualfeedback
    if ($template->ispublic) {
        $s_context = context_system::instance();
    } else {
        $s_context = context_course::instance($individualfeedback->course);
    }
    $course = $DB->get_record('course', array('id'=>$individualfeedback->course));
    $cm = get_coursemodule_from_instance('individualfeedback', $individualfeedback->id);
    $f_context = context_module::instance($cm->id);

    //if deleteold then delete all old items before
    //get all items
    $deleteold = $formdata->deleteolditems;
    if ($deleteold) {
        if ($individualfeedbackitems = $DB->get_records('individualfeedback_item', array('individualfeedback'=>$individualfeedback->id))) {
            //delete all items of this individualfeedback
            foreach ($individualfeedbackitems as $item) {
                individualfeedback_delete_item($item->id, false);
            }

            $params = array('individualfeedback'=>$individualfeedback->id);
            if ($completeds = $DB->get_records('individualfeedback_completed', $params)) {
                $completion = new completion_info($course);
                foreach ($completeds as $completed) {
                    $DB->delete_records('individualfeedback_completed', array('id' => $completed->id));
                    // Update completion state
                    if ($completion->is_enabled($cm) && $cm->completion == COMPLETION_TRACKING_AUTOMATIC &&
                            $individualfeedback->completionsubmit) {
                        $completion->update_state($cm, COMPLETION_INCOMPLETE, $completed->userid);
                    }
                }
            }
            $DB->delete_records('indfeedback_completedtmp', array('individualfeedback'=>$individualfeedback->id));
        }
        $positionoffset = 0;
    } else {
        //if the old items are kept the new items will be appended
        //therefor the new position has an offset
        $positionoffset = $DB->count_records('individualfeedback_item', array('individualfeedback'=>$individualfeedback->id));
    }

    //create items of this new template
    //depend items we are storing temporary in an mapping list array(new id => dependitem)
    //we also store a mapping of all items array(oldid => newid)
    $dependitemsmap = array();
    $itembackup = array();
    foreach ($templitems as $t_item) {
        // Only import the selected items.
        $formkey = 'import_' . $t_item->id;
        if (!isset($formdata->$formkey) || !$formdata->$formkey) {
            continue;
        }
        $item = clone($t_item);
        unset($item->id);
        $item->individualfeedback = $individualfeedback->id;
        $item->template = 0;
        $item->position = $item->position + $positionoffset;

        $item->id = $DB->insert_record('individualfeedback_item', $item);

        //moving the files to the new item
        $templatefiles = $fs->get_area_files($s_context->id,
                                        'mod_individualfeedback',
                                        'template',
                                        $t_item->id,
                                        "id",
                                        false);
        if ($templatefiles) {
            foreach ($templatefiles as $tfile) {
                $file_record = new stdClass();
                $file_record->contextid = $f_context->id;
                $file_record->component = 'mod_individualfeedback';
                $file_record->filearea = 'item';
                $file_record->itemid = $item->id;
                $fs->create_file_from_storedfile($file_record, $tfile);
            }
        }

        $itembackup[$t_item->id] = $item->id;
        if ($item->dependitem) {
            $dependitemsmap[$item->id] = $item->dependitem;
        }
    }

    //remapping the dependency
    foreach ($dependitemsmap as $key => $dependitem) {
        $newitem = $DB->get_record('individualfeedback_item', array('id'=>$key));
        $newitem->dependitem = $itembackup[$newitem->dependitem];
        $DB->update_record('individualfeedback_item', $newitem);
    }
}

/**
 * get the list of available templates.
 * if the $onlyown param is set true so only templates from own course will be served
 * this is important for droping templates
 *
 * @global object
 * @param object $course
 * @param string $onlyownorpublic
 * @return array the template recordsets
 */
function individualfeedback_get_template_list($course, $onlyownorpublic = '') {
    global $DB, $CFG;

    switch($onlyownorpublic) {
        case '':
            $templates = $DB->get_records_select('individualfeedback_template',
                                                 'course = ? OR ispublic = 1',
                                                 array($course->id),
                                                 'name');
            break;
        case 'own':
            $templates = $DB->get_records('individualfeedback_template',
                                          array('course'=>$course->id),
                                          'name');
            break;
        case 'public':
            $templates = $DB->get_records('individualfeedback_template', array('ispublic'=>1), 'name');
            break;
    }
    return $templates;
}

/**
 * Get the items of a template
 *
 * @param int $templateid
 * @return array the template items
 */
function individualfeedback_get_template_items($templateid) {
    global $DB;

    return $DB->get_records('individualfeedback_item', array('template' => $templateid), 'position');
}

////////////////////////////////////////////////
//Handling der Items
////////////////////////////////////////////////
////////////////////////////////////////////////

/**
 * load the lib.php from item-plugin-dir and returns the instance of the itemclass
 *
 * @param string $typ
 * @return individualfeedback_item_base the instance of itemclass
 */
function individualfeedback_get_item_class($typ) {
    global $CFG;

    //get the class of item-typ
    $itemclass = 'individualfeedback_item_'.$typ;
    //get the instance of item-class
    if (!class_exists($itemclass)) {
        require_once($CFG->dirroot.'/mod/individualfeedback/item/'.$typ.'/lib.php');
    }
    return new $itemclass();
}

/**
 * load the available item plugins from given subdirectory of $CFG->dirroot
 * the default is "mod/individualfeedback/item"
 *
 * @global object
 * @param string $dir the subdir
 * @return array pluginnames as string
 */
function individualfeedback_load_individualfeedback_items($dir = 'mod/individualfeedback/item') {
    global $CFG;
    $names = get_list_of_plugins($dir);
    $ret_names = array();

    foreach ($names as $name) {
        require_once($CFG->dirroot.'/'.$dir.'/'.$name.'/lib.php');
        if (class_exists('individualfeedback_item_'.$name)) {
            $ret_names[] = $name;
        }
    }
    return $ret_names;
}

/**
 * load the available item plugins to use as dropdown-options
 *
 * @global object
 * @return array pluginnames as string
 */
function individualfeedback_load_individualfeedback_items_options() {
    global $CFG;

    $individualfeedback_options = array("pagebreak" => get_string('add_pagebreak', 'individualfeedback'));
    $individualfeedback_options['questiongroup'] = get_string('questiongroup', 'individualfeedback');

    if (!$individualfeedback_names = individualfeedback_load_individualfeedback_items('mod/individualfeedback/item')) {
        return array();
    }

    foreach ($individualfeedback_names as $fn) {
        $individualfeedback_options[$fn] = get_string($fn, 'individualfeedback');
    }
    asort($individualfeedback_options);
    return $individualfeedback_options;
}

/**
 * load the available items for the depend item dropdown list shown in the edit_item form
 *
 * @global object
 * @param object $individualfeedback
 * @param object $item the item of the edit_item form
 * @return array all items except the item $item, labels and pagebreaks
 */
function individualfeedback_get_depend_candidates_for_item($individualfeedback, $item) {
    global $DB;
    //all items for dependitem
    $where = "individualfeedback = ? AND typ != 'pagebreak' AND hasvalue = 1";
    $params = array($individualfeedback->id);
    if (isset($item->id) AND $item->id) {
        $where .= ' AND id != ?';
        $params[] = $item->id;
    }
    $dependitems = array(0 => get_string('choose'));
    $individualfeedbackitems = $DB->get_records_select_menu('individualfeedback_item',
                                                  $where,
                                                  $params,
                                                  'position',
                                                  'id, label');

    if (!$individualfeedbackitems) {
        return $dependitems;
    }
    //adding the choose-option
    foreach ($individualfeedbackitems as $key => $val) {
        if (trim(strval($val)) !== '') {
            $dependitems[$key] = format_string($val);
        }
    }
    return $dependitems;
}

/**
 * creates a new item-record
 *
 * @deprecated since 3.1
 * @param object $data the data from edit_item_form
 * @return int the new itemid
 */
function individualfeedback_create_item($data) {
    debugging('Function individualfeedback_create_item() is deprecated because it was not used.',
            DEBUG_DEVELOPER);
    global $DB;

    $item = new stdClass();
    $item->individualfeedback = $data->individualfeedbackid;

    $item->template=0;
    if (isset($data->templateid)) {
            $item->template = intval($data->templateid);
    }

    $itemname = trim($data->itemname);
    $item->name = ($itemname ? $data->itemname : get_string('no_itemname', 'individualfeedback'));

    if (!empty($data->itemlabel)) {
        $item->label = trim($data->itemlabel);
    } else {
        $item->label = get_string('no_itemlabel', 'individualfeedback');
    }

    $itemobj = individualfeedback_get_item_class($data->typ);
    $item->presentation = ''; //the date comes from postupdate() of the itemobj

    $item->hasvalue = $itemobj->get_hasvalue();

    $item->typ = $data->typ;
    $item->position = $data->position;

    $item->required=0;
    if (!empty($data->required)) {
        $item->required = $data->required;
    }

    $item->id = $DB->insert_record('individualfeedback_item', $item);

    //move all itemdata to the data
    $data->id = $item->id;
    $data->individualfeedback = $item->individualfeedback;
    $data->name = $item->name;
    $data->label = $item->label;
    $data->required = $item->required;
    return $itemobj->postupdate($data);
}

/**
 * save the changes of a given item.
 *
 * @global object
 * @param object $item
 * @return boolean
 */
function individualfeedback_update_item($item) {
    global $DB;
    return $DB->update_record("individualfeedback_item", $item);
}

/**
 * deletes an item and also deletes all related values
 *
 * @global object
 * @uses CONTEXT_MODULE
 * @param int $itemid
 * @param boolean $renumber should the kept items renumbered Yes/No
 * @param object $template if the template is given so the items are bound to it
 * @return void
 */
function individualfeedback_delete_item($itemid, $renumber = true, $template = false) {
    global $DB;

    // SFSUBM-27 - Make sure it isn't deleted by individualfeedback_delete_group_items yet.
    if (!$item = $DB->get_record('individualfeedback_item', array('id' => $itemid))) {
        return;
    }

    // If we remove a group, make sure all group questions get deleted as well.
    if ($item->typ == 'questiongroup') {
        individualfeedback_delete_group_items($item);
    }

    //deleting the files from the item
    $fs = get_file_storage();

    if ($template) {
        if ($template->ispublic) {
            $context = context_system::instance();
        } else {
            $context = context_course::instance($template->course);
        }
        $templatefiles = $fs->get_area_files($context->id,
                                    'mod_individualfeedback',
                                    'template',
                                    $item->id,
                                    "id",
                                    false);

        if ($templatefiles) {
            $fs->delete_area_files($context->id, 'mod_individualfeedback', 'template', $item->id);
        }
    } else {
        if (!$cm = get_coursemodule_from_instance('individualfeedback', $item->individualfeedback)) {
            return false;
        }
        $context = context_module::instance($cm->id);

        $itemfiles = $fs->get_area_files($context->id,
                                    'mod_individualfeedback',
                                    'item',
                                    $item->id,
                                    "id", false);

        if ($itemfiles) {
            $fs->delete_area_files($context->id, 'mod_individualfeedback', 'item', $item->id);
        }
    }

    $DB->delete_records("individualfeedback_value", array("item"=>$itemid));
    $DB->delete_records("individualfeedback_valuetmp", array("item"=>$itemid));

    //remove all depends
    $DB->set_field('individualfeedback_item', 'dependvalue', '', array('dependitem'=>$itemid));
    $DB->set_field('individualfeedback_item', 'dependitem', 0, array('dependitem'=>$itemid));

    $DB->delete_records("individualfeedback_item", array("id"=>$itemid));
    if ($renumber) {
        individualfeedback_renumber_items($item->individualfeedback);
    }
}

/**
 * deletes all items of the given group, before groups get's deleted.
 *
 * @global object
 * @param int $individualfeedbackid
 * @return void
 */
function individualfeedback_delete_group_items($groupitem) {
    global $DB;

    if (!$endgroupitem = $DB->get_record('individualfeedback_item', array('dependitem' => $groupitem->id, 'typ' => 'questiongroupend'))) {
        return false;
    }

    $where = 'individualfeedback = :individualfeedback AND template = :template
                AND position > :startposition AND position <= :endposition';
    $params = array('individualfeedback' => $groupitem->individualfeedback, 'template' => $groupitem->template,
                        'startposition' => $groupitem->position, 'endposition' => $endgroupitem->position);

    if ($groupitems = $DB->get_records_select('individualfeedback_item', $where, $params)) {
        foreach ($groupitems as $item) {
            individualfeedback_delete_item($item->id);
        }
    }
}

/**
 * deletes all items of the given individualfeedbackid
 *
 * @global object
 * @param int $individualfeedbackid
 * @return void
 */
function individualfeedback_delete_all_items($individualfeedbackid) {
    global $DB, $CFG;
    require_once($CFG->libdir.'/completionlib.php');

    if (!$individualfeedback = $DB->get_record('individualfeedback', array('id'=>$individualfeedbackid))) {
        return false;
    }

    if (!$cm = get_coursemodule_from_instance('individualfeedback', $individualfeedback->id)) {
        return false;
    }

    if (!$course = $DB->get_record('course', array('id'=>$individualfeedback->course))) {
        return false;
    }

    if (!$items = $DB->get_records('individualfeedback_item', array('individualfeedback'=>$individualfeedbackid))) {
        return;
    }
    foreach ($items as $item) {
        individualfeedback_delete_item($item->id, false);
    }
    if ($completeds = $DB->get_records('individualfeedback_completed', array('individualfeedback'=>$individualfeedback->id))) {
        $completion = new completion_info($course);
        foreach ($completeds as $completed) {
            $DB->delete_records('individualfeedback_completed', array('id' => $completed->id));
            // Update completion state
            if ($completion->is_enabled($cm) && $cm->completion == COMPLETION_TRACKING_AUTOMATIC &&
                    $individualfeedback->completionsubmit) {
                $completion->update_state($cm, COMPLETION_INCOMPLETE, $completed->userid);
            }
        }
    }

    $DB->delete_records('indfeedback_completedtmp', array('individualfeedback'=>$individualfeedbackid));

}

/**
 * this function toggled the item-attribute required (yes/no)
 *
 * @global object
 * @param object $item
 * @return boolean
 */
function individualfeedback_switch_item_required($item) {
    global $DB, $CFG;

    $itemobj = individualfeedback_get_item_class($item->typ);

    if ($itemobj->can_switch_require()) {
        $new_require_val = (int)!(bool)$item->required;
        $params = array('id'=>$item->id);
        $DB->set_field('individualfeedback_item', 'required', $new_require_val, $params);
    }
    return true;
}

/**
 * renumbers all items of the given individualfeedbackid
 *
 * @global object
 * @param int $individualfeedbackid
 * @return void
 */
function individualfeedback_renumber_items($individualfeedbackid) {
    global $DB;

    $items = $DB->get_records('individualfeedback_item', array('individualfeedback'=>$individualfeedbackid), 'position');
    $pos = 1;
    if ($items) {
        foreach ($items as $item) {
            $DB->set_field('individualfeedback_item', 'position', $pos, array('id'=>$item->id));
            $pos++;
        }
    }
}

/**
 * this decreases the position of the given item
 *
 * @global object
 * @param object $item
 * @return bool
 */
function individualfeedback_moveup_item($item) {
    global $DB;

    if ($item->position == 1) {
        return true;
    }

    $params = array('individualfeedback'=>$item->individualfeedback);
    if (!$items = $DB->get_records('individualfeedback_item', $params, 'position')) {
        return false;
    }

    $itembefore = null;
    foreach ($items as $i) {
        if ($i->id == $item->id) {
            if (is_null($itembefore)) {
                return true;
            }
            $itembefore->position = $item->position;
            $item->position--;
            individualfeedback_update_item($itembefore);
            individualfeedback_update_item($item);
            individualfeedback_renumber_items($item->individualfeedback);
            return true;
        }
        $itembefore = $i;
    }
    return false;
}

/**
 * this increased the position of the given item
 *
 * @global object
 * @param object $item
 * @return bool
 */
function individualfeedback_movedown_item($item) {
    global $DB;

    $params = array('individualfeedback'=>$item->individualfeedback);
    if (!$items = $DB->get_records('individualfeedback_item', $params, 'position')) {
        return false;
    }

    $movedownitem = null;
    foreach ($items as $i) {
        if (!is_null($movedownitem) AND $movedownitem->id == $item->id) {
            $movedownitem->position = $i->position;
            $i->position--;
            individualfeedback_update_item($movedownitem);
            individualfeedback_update_item($i);
            individualfeedback_renumber_items($item->individualfeedback);
            return true;
        }
        $movedownitem = $i;
    }
    return false;
}

/**
 * here the position of the given item will be set to the value in $pos
 *
 * @global object
 * @param object $moveitem
 * @param int $pos
 * @return boolean
 */
function individualfeedback_move_item($moveitem, $pos) {
    global $DB;

    $params = array('individualfeedback'=>$moveitem->individualfeedback);
    if (!$allitems = $DB->get_records('individualfeedback_item', $params, 'position')) {
        return false;
    }
    if (is_array($allitems)) {
        $index = 1;
        foreach ($allitems as $item) {
            if ($index == $pos) {
                $index++;
            }
            if ($item->id == $moveitem->id) {
                $moveitem->position = $pos;
                individualfeedback_update_item($moveitem);
                continue;
            }
            $item->position = $index;
            individualfeedback_update_item($item);
            $index++;
        }
        return true;
    }
    return false;
}

/**
 * prints the given item as a preview.
 * each item-class has an own print_item_preview function implemented.
 *
 * @deprecated since Moodle 3.1
 * @global object
 * @param object $item the item what we want to print out
 * @return void
 */
function individualfeedback_print_item_preview($item) {
    debugging('Function individualfeedback_print_item_preview() is deprecated and does nothing. '
            . 'Items must implement complete_form_element()', DEBUG_DEVELOPER);
}

/**
 * prints the given item in the completion form.
 * each item-class has an own print_item_complete function implemented.
 *
 * @deprecated since Moodle 3.1
 * @param object $item the item what we want to print out
 * @param mixed $value the value
 * @param boolean $highlightrequire if this set true and the value are false on completing so the item will be highlighted
 * @return void
 */
function individualfeedback_print_item_complete($item, $value = false, $highlightrequire = false) {
    debugging('Function individualfeedback_print_item_complete() is deprecated and does nothing. '
            . 'Items must implement complete_form_element()', DEBUG_DEVELOPER);
}

/**
 * prints the given item in the show entries page.
 * each item-class has an own print_item_show_value function implemented.
 *
 * @deprecated since Moodle 3.1
 * @param object $item the item what we want to print out
 * @param mixed $value
 * @return void
 */
function individualfeedback_print_item_show_value($item, $value = false) {
    debugging('Function individualfeedback_print_item_show_value() is deprecated and does nothing. '
            . 'Items must implement complete_form_element()', DEBUG_DEVELOPER);
}

/**
 * if the user completes a individualfeedback and there is a pagebreak so the values are saved temporary.
 * the values are not saved permanently until the user click on save button
 *
 * @global object
 * @param object $individualfeedbackcompleted
 * @return object temporary saved completed-record
 */
function individualfeedback_set_tmp_values($individualfeedbackcompleted) {
    global $DB;

    //first we create a completedtmp
    $tmpcpl = new stdClass();
    foreach ($individualfeedbackcompleted as $key => $value) {
        $tmpcpl->{$key} = $value;
    }
    unset($tmpcpl->id);
    $tmpcpl->timemodified = time();
    $tmpcpl->id = $DB->insert_record('indfeedback_completedtmp', $tmpcpl);
    //get all values of original-completed
    if (!$values = $DB->get_records('individualfeedback_value', array('completed'=>$individualfeedbackcompleted->id))) {
        return;
    }
    foreach ($values as $value) {
        unset($value->id);
        $value->completed = $tmpcpl->id;
        $DB->insert_record('individualfeedback_valuetmp', $value);
    }
    return $tmpcpl;
}

/**
 * this saves the temporary saved values permanently
 *
 * @global object
 * @param object $individualfeedbackcompletedtmp the temporary completed
 * @param object $individualfeedbackcompleted the target completed
 * @return int the id of the completed
 */
function individualfeedback_save_tmp_values($individualfeedbackcompletedtmp, $individualfeedbackcompleted) {
    global $DB;

    $tmpcplid = $individualfeedbackcompletedtmp->id;
    if ($individualfeedbackcompleted) {
        //first drop all existing values
        $DB->delete_records('individualfeedback_value', array('completed'=>$individualfeedbackcompleted->id));
        //update the current completed
        $individualfeedbackcompleted->timemodified = time();
        $DB->update_record('individualfeedback_completed', $individualfeedbackcompleted);
    } else {
        $individualfeedbackcompleted = clone($individualfeedbackcompletedtmp);
        $individualfeedbackcompleted->id = '';
        $individualfeedbackcompleted->timemodified = time();
        $individualfeedbackcompleted->id = $DB->insert_record('individualfeedback_completed', $individualfeedbackcompleted);
    }

    $allitems = $DB->get_records('individualfeedback_item', array('individualfeedback' => $individualfeedbackcompleted->individualfeedback));

    //save all the new values from individualfeedback_valuetmp
    //get all values of tmp-completed
    $params = array('completed'=>$individualfeedbackcompletedtmp->id);
    $values = $DB->get_records('individualfeedback_valuetmp', $params);
    foreach ($values as $value) {
        //check if there are depend items
        $item = $DB->get_record('individualfeedback_item', array('id'=>$value->item));
        if ($item->dependitem > 0 && isset($allitems[$item->dependitem])) {
            $check = individualfeedback_compare_item_value($tmpcplid,
                                        $allitems[$item->dependitem],
                                        $item->dependvalue,
                                        true);
        } else {
            $check = true;
        }
        if ($check) {
            unset($value->id);
            $value->completed = $individualfeedbackcompleted->id;
            $DB->insert_record('individualfeedback_value', $value);
        }
    }
    //drop all the tmpvalues
    $DB->delete_records('individualfeedback_valuetmp', array('completed'=>$tmpcplid));
    $DB->delete_records('indfeedback_completedtmp', array('id'=>$tmpcplid));

    // Trigger event for the delete action we performed.
    $cm = get_coursemodule_from_instance('individualfeedback', $individualfeedbackcompleted->individualfeedback);
    $event = \mod_individualfeedback\event\response_submitted::create_from_record($individualfeedbackcompleted, $cm);
    $event->trigger();
    return $individualfeedbackcompleted->id;

}

/**
 * deletes the given temporary completed and all related temporary values
 *
 * @deprecated since Moodle 3.1
 *
 * @param int $tmpcplid
 * @return void
 */
function individualfeedback_delete_completedtmp($tmpcplid) {
    global $DB;

    debugging('Function individualfeedback_delete_completedtmp() is deprecated because it is no longer used',
            DEBUG_DEVELOPER);

    $DB->delete_records('individualfeedback_valuetmp', array('completed'=>$tmpcplid));
    $DB->delete_records('indfeedback_completedtmp', array('id'=>$tmpcplid));
}

////////////////////////////////////////////////
////////////////////////////////////////////////
////////////////////////////////////////////////
//functions to handle the pagebreaks
////////////////////////////////////////////////

/**
 * this creates a pagebreak.
 * a pagebreak is a special kind of item
 *
 * @global object
 * @param int $individualfeedbackid
 * @return mixed false if there already is a pagebreak on last position or the id of the pagebreak-item
 */
function individualfeedback_create_pagebreak($individualfeedbackid) {
    global $DB;

    //check if there already is a pagebreak on the last position
    $lastposition = $DB->count_records('individualfeedback_item', array('individualfeedback'=>$individualfeedbackid));
    if ($lastposition == individualfeedback_get_last_break_position($individualfeedbackid)) {
        return false;
    }

    $item = new stdClass();
    $item->individualfeedback = $individualfeedbackid;

    $item->template=0;

    $item->name = '';

    $item->presentation = '';
    $item->hasvalue = 0;

    $item->typ = 'pagebreak';
    $item->position = $lastposition + 1;

    $item->required=0;

    return $DB->insert_record('individualfeedback_item', $item);
}

/**
 * get all positions of pagebreaks in the given individualfeedback
 *
 * @global object
 * @param int $individualfeedbackid
 * @return array all ordered pagebreak positions
 */
function individualfeedback_get_all_break_positions($individualfeedbackid) {
    global $DB;

    $params = array('typ'=>'pagebreak', 'individualfeedback'=>$individualfeedbackid);
    $allbreaks = $DB->get_records_menu('individualfeedback_item', $params, 'position', 'id, position');
    if (!$allbreaks) {
        return false;
    }
    return array_values($allbreaks);
}

/**
 * get the position of the last pagebreak
 *
 * @param int $individualfeedbackid
 * @return int the position of the last pagebreak
 */
function individualfeedback_get_last_break_position($individualfeedbackid) {
    if (!$allbreaks = individualfeedback_get_all_break_positions($individualfeedbackid)) {
        return false;
    }
    return $allbreaks[count($allbreaks) - 1];
}

/**
 * this returns the position where the user can continue the completing.
 *
 * @deprecated since Moodle 3.1
 * @global object
 * @global object
 * @global object
 * @param int $individualfeedbackid
 * @param int $courseid
 * @param string $guestid this id will be saved temporary and is unique
 * @return int the position to continue
 */
function individualfeedback_get_page_to_continue($individualfeedbackid, $courseid = false, $guestid = false) {
    global $CFG, $USER, $DB;

    debugging('Function individualfeedback_get_page_to_continue() is deprecated and since it is '
            . 'no longer used in mod_individualfeedback', DEBUG_DEVELOPER);

    //is there any break?

    if (!$allbreaks = individualfeedback_get_all_break_positions($individualfeedbackid)) {
        return false;
    }

    $params = array();
    if ($courseid) {
        $courseselect = "AND fv.course_id = :courseid";
        $params['courseid'] = $courseid;
    } else {
        $courseselect = '';
    }

    if ($guestid) {
        $userselect = "AND fc.guestid = :guestid";
        $usergroup = "GROUP BY fc.guestid";
        $params['guestid'] = $guestid;
    } else {
        $userselect = "AND fc.userid = :userid";
        $usergroup = "GROUP BY fc.userid";
        $params['userid'] = $USER->id;
    }

    $sql =  "SELECT MAX(fi.position)
               FROM {indfeedback_completedtmp} fc, {individualfeedback_valuetmp} fv, {individualfeedback_item} fi
              WHERE fc.id = fv.completed
                    $userselect
                    AND fc.individualfeedback = :individualfeedbackid
                    $courseselect
                    AND fi.id = fv.item
         $usergroup";
    $params['individualfeedbackid'] = $individualfeedbackid;

    $lastpos = $DB->get_field_sql($sql, $params);

    //the index of found pagebreak is the searched pagenumber
    foreach ($allbreaks as $pagenr => $br) {
        if ($lastpos < $br) {
            return $pagenr;
        }
    }
    return count($allbreaks);
}

////////////////////////////////////////////////
////////////////////////////////////////////////
////////////////////////////////////////////////
//functions to handle the values
////////////////////////////////////////////////

/**
 * cleans the userinput while submitting the form.
 *
 * @deprecated since Moodle 3.1
 * @param mixed $value
 * @return mixed
 */
function individualfeedback_clean_input_value($item, $value) {
    debugging('Function individualfeedback_clean_input_value() is deprecated and does nothing. '
            . 'Items must implement complete_form_element()', DEBUG_DEVELOPER);
}

/**
 * this saves the values of an completed.
 * if the param $tmp is set true so the values are saved temporary in table individualfeedback_valuetmp.
 * if there is already a completed and the userid is set so the values are updated.
 * on all other things new value records will be created.
 *
 * @deprecated since Moodle 3.1
 *
 * @param int $usrid
 * @param boolean $tmp
 * @return mixed false on error or the completeid
 */
function individualfeedback_save_values($usrid, $tmp = false) {
    global $DB;

    debugging('Function individualfeedback_save_values() was deprecated because it did not have '.
            'enough arguments, was not suitable for non-temporary table and was taking '.
            'data directly from input', DEBUG_DEVELOPER);

    $completedid = optional_param('completedid', 0, PARAM_INT);
    $table = 'individualfeedback_completed';
    if ($tmp) {
        $table = 'indfeedback_completedtmp';
    }
    $time = time();
    $timemodified = mktime(0, 0, 0, date('m', $time), date('d', $time), date('Y', $time));

    if ($usrid == 0) {
        return individualfeedback_create_values($usrid, $timemodified, $tmp);
    }
    $completed = $DB->get_record($table, array('id'=>$completedid));
    if (!$completed) {
        return individualfeedback_create_values($usrid, $timemodified, $tmp);
    } else {
        $completed->timemodified = $timemodified;
        return individualfeedback_update_values($completed, $tmp);
    }
}

/**
 * this saves the values from anonymous user such as guest on the main-site
 *
 * @deprecated since Moodle 3.1
 *
 * @param string $guestid the unique guestidentifier
 * @return mixed false on error or the completeid
 */
function individualfeedback_save_guest_values($guestid) {
    global $DB;

    debugging('Function individualfeedback_save_guest_values() was deprecated because it did not have '.
            'enough arguments, was not suitable for non-temporary table and was taking '.
            'data directly from input', DEBUG_DEVELOPER);

    $completedid = optional_param('completedid', false, PARAM_INT);

    $timemodified = time();
    if (!$completed = $DB->get_record('indfeedback_completedtmp', array('id'=>$completedid))) {
        return individualfeedback_create_values(0, $timemodified, true, $guestid);
    } else {
        $completed->timemodified = $timemodified;
        return individualfeedback_update_values($completed, true);
    }
}

/**
 * get the value from the given item related to the given completed.
 * the value can come as temporary or as permanently value. the deciding is done by $tmp
 *
 * @global object
 * @param int $completeid
 * @param int $itemid
 * @param boolean $tmp
 * @return mixed the value, the type depends on plugin-definition
 */
function individualfeedback_get_item_value($completedid, $itemid, $tmp = false) {
    global $DB;

    $tmpstr = $tmp ? 'tmp' : '';
    $params = array('completed'=>$completedid, 'item'=>$itemid);
    return $DB->get_field('individualfeedback_value'.$tmpstr, 'value', $params);
}

/**
 * compares the value of the itemid related to the completedid with the dependvalue.
 * this is used if a depend item is set.
 * the value can come as temporary or as permanently value. the deciding is done by $tmp.
 *
 * @param int $completedid
 * @param stdClass|int $item
 * @param mixed $dependvalue
 * @param bool $tmp
 * @return bool
 */
function individualfeedback_compare_item_value($completedid, $item, $dependvalue, $tmp = false) {
    global $DB;

    if (is_int($item)) {
        $item = $DB->get_record('individualfeedback_item', array('id' => $item));
    }

    $dbvalue = individualfeedback_get_item_value($completedid, $item->id, $tmp);

    $itemobj = individualfeedback_get_item_class($item->typ);
    return $itemobj->compare_value($item, $dbvalue, $dependvalue); //true or false
}

/**
 * this function checks the correctness of values.
 * the rules for this are implemented in the class of each item.
 * it can be the required attribute or the value self e.g. numeric.
 * the params first/lastitem are given to determine the visible range between pagebreaks.
 *
 * @global object
 * @param int $firstitem the position of firstitem for checking
 * @param int $lastitem the position of lastitem for checking
 * @return boolean
 */
function individualfeedback_check_values($firstitem, $lastitem) {
    debugging('Function individualfeedback_check_values() is deprecated and does nothing. '
            . 'Items must implement complete_form_element()', DEBUG_DEVELOPER);
    return true;
}

/**
 * this function create a complete-record and the related value-records.
 * depending on the $tmp (true/false) the values are saved temporary or permanently
 *
 * @deprecated since Moodle 3.1
 *
 * @param int $userid
 * @param int $timemodified
 * @param boolean $tmp
 * @param string $guestid a unique identifier to save temporary data
 * @return mixed false on error or the completedid
 */
function individualfeedback_create_values($usrid, $timemodified, $tmp = false, $guestid = false) {
    global $DB;

    debugging('Function individualfeedback_create_values() was deprecated because it did not have '.
            'enough arguments, was not suitable for non-temporary table and was taking '.
            'data directly from input', DEBUG_DEVELOPER);

    $tmpstr = $tmp ? 'tmp' : '';
    $table = 'individualfeedback_completed';
    if ($tmp) {
        $table = 'indfeedback_completedtmp';
    }
    //first we create a new completed record
    $completed = new stdClass();
    $completed->individualfeedback           = $individualfeedbackid;
    $completed->userid             = individualfeedback_hash_userid($usrid);
    $completed->guestid            = $guestid;
    $completed->timemodified       = $timemodified;
    $completed->anonymous_response = $anonymous_response;

    $completedid = $DB->insert_record($table, $completed);

    $completed = $DB->get_record($table, array('id'=>$completedid));

    //the keys are in the form like abc_xxx
    //with explode we make an array with(abc, xxx) and (abc=typ und xxx=itemnr)

    //get the items of the individualfeedback
    if (!$allitems = $DB->get_records('individualfeedback_item', array('individualfeedback'=>$completed->individualfeedback))) {
        return false;
    }
    foreach ($allitems as $item) {
        if (!$item->hasvalue) {
            continue;
        }
        //get the class of item-typ
        $itemobj = individualfeedback_get_item_class($item->typ);

        $keyname = $item->typ.'_'.$item->id;

        if ($item->typ === 'multichoice') {
            $itemvalue = optional_param_array($keyname, null, PARAM_INT);
        } else {
            $itemvalue = optional_param($keyname, null, PARAM_NOTAGS);
        }

        if (is_null($itemvalue)) {
            continue;
        }

        $value = new stdClass();
        $value->item = $item->id;
        $value->completed = $completed->id;
        $value->course_id = $courseid;

        //the kind of values can be absolutely different
        //so we run create_value directly by the item-class
        $value->value = $itemobj->create_value($itemvalue);
        $DB->insert_record('individualfeedback_value'.$tmpstr, $value);
    }
    return $completed->id;
}

/**
 * this function updates a complete-record and the related value-records.
 * depending on the $tmp (true/false) the values are saved temporary or permanently
 *
 * @global object
 * @param object $completed
 * @param boolean $tmp
 * @return int the completedid
 */
function individualfeedback_update_values($completed, $tmp = false) {
    global $DB;

    debugging('Function individualfeedback_update_values() was deprecated because it did not have '.
            'enough arguments, was not suitable for non-temporary table and was taking '.
            'data directly from input', DEBUG_DEVELOPER);

    $courseid = optional_param('courseid', false, PARAM_INT);
    $tmpstr = $tmp ? 'tmp' : '';
    $table = 'individualfeedback_completed';
    if ($tmp) {
        $table = 'indfeedback_completedtmp';
    }

    $DB->update_record($table, $completed);
    //get the values of this completed
    $values = $DB->get_records('individualfeedback_value'.$tmpstr, array('completed'=>$completed->id));

    //get the items of the individualfeedback
    if (!$allitems = $DB->get_records('individualfeedback_item', array('individualfeedback'=>$completed->individualfeedback))) {
        return false;
    }
    foreach ($allitems as $item) {
        if (!$item->hasvalue) {
            continue;
        }
        //get the class of item-typ
        $itemobj = individualfeedback_get_item_class($item->typ);

        $keyname = $item->typ.'_'.$item->id;

        if ($item->typ === 'multichoice') {
            $itemvalue = optional_param_array($keyname, null, PARAM_INT);
        } else {
            $itemvalue = optional_param($keyname, null, PARAM_NOTAGS);
        }

        //is the itemvalue set (could be a subset of items because pagebreak)?
        if (is_null($itemvalue)) {
            continue;
        }

        $newvalue = new stdClass();
        $newvalue->item = $item->id;
        $newvalue->completed = $completed->id;
        $newvalue->course_id = $courseid;

        //the kind of values can be absolutely different
        //so we run create_value directly by the item-class
        $newvalue->value = $itemobj->create_value($itemvalue);

        //check, if we have to create or update the value
        $exist = false;
        foreach ($values as $value) {
            if ($value->item == $newvalue->item) {
                $newvalue->id = $value->id;
                $exist = true;
                break;
            }
        }
        if ($exist) {
            $DB->update_record('individualfeedback_value'.$tmpstr, $newvalue);
        } else {
            $DB->insert_record('individualfeedback_value'.$tmpstr, $newvalue);
        }
    }

    return $completed->id;
}

/**
 * get the values of an item depending on the given groupid.
 * if the individualfeedback is anonymous so the values are shuffled
 *
 * @global object
 * @global object
 * @param object $item
 * @param int $groupid
 * @param int $courseid
 * @param bool $ignore_empty if this is set true so empty values are not delivered
 * @return array the value-records
 */
function individualfeedback_get_group_values($item,
                                   $groupid = false,
                                   $courseid = false,
                                   $negative_formulated = false,
                                   $ignore_empty = false,
                                   $selfassessment = false) {

    global $CFG, $DB, $USER;

    // Get the values except for the self assessment values.
    $params = array('selfassessment' => (int) $selfassessment);
    if ($ignore_empty) {
        $value = $DB->sql_compare_text('value');
        $ignore_empty_select = "AND $value != :emptyvalue AND $value != :zerovalue";
        $params += array('emptyvalue' => '', 'zerovalue' => '0');
    } else {
        $ignore_empty_select = "";
    }

    if ($courseid) {
        $select = "item = :itemid AND course_id = :courseid ".$ignore_empty_select;
        $params += array('itemid' => $item->id, 'courseid' => $courseid);
    } else {
        $select = "item = :itemid ".$ignore_empty_select;
        $params += array('itemid' => $item->id);
    }
    $sql = "SELECT iv.*
    FROM {individualfeedback_value} iv
    JOIN {individualfeedback_completed} ic ON iv.completed = ic.id
    WHERE {$select}
    AND ic.selfassessment = :selfassessment";

    // SFSUBM-26 - only show own users selfassessment.
    if ($selfassessment) {
        $sql .= "AND userid = :userid ";
        $params['userid'] = individualfeedback_hash_userid($USER->id);
    }

    $values = $DB->get_records_sql($sql, $params);

    $params = array('id' => $item->individualfeedback);
    if ($DB->get_field('individualfeedback', 'anonymous', $params) == INDIVIDUALFEEDBACK_ANONYMOUS_YES) {
        if (is_array($values)) {
            shuffle($values);
        }
    }

    return $values;
}


/**
 * check for multiple_submit = false.
 * if the individualfeedback is global so the courseid must be given
 *
 * @global object
 * @global object
 * @param int $individualfeedbackid
 * @param int $courseid
 * @return boolean true if the individualfeedback already is submitted otherwise false
 */
function individualfeedback_is_already_submitted($individualfeedbackid, $courseid = false) {
    global $USER, $DB;

    if (!isloggedin() || isguestuser()) {
        return false;
    }

    $params = array('userid' => individualfeedback_hash_userid($USER->id), 'individualfeedback' => $individualfeedbackid);
    if ($courseid) {
        $params['courseid'] = $courseid;
    }
    return $DB->record_exists('individualfeedback_completed', $params);
}

/**
 * if the completion of a individualfeedback will be continued eg.
 * by pagebreak or by multiple submit so the complete must be found.
 * if the param $tmp is set true so all things are related to temporary completeds
 *
 * @deprecated since Moodle 3.1
 * @param int $individualfeedbackid
 * @param boolean $tmp
 * @param int $courseid
 * @param string $guestid
 * @return int the id of the found completed
 */
function individualfeedback_get_current_completed($individualfeedbackid,
                                        $tmp = false,
                                        $courseid = false,
                                        $guestid = false) {

    debugging('Function individualfeedback_get_current_completed() is deprecated. Please use either '.
            'individualfeedback_get_current_completed_tmp() or individualfeedback_get_last_completed()',
            DEBUG_DEVELOPER);

    global $USER, $CFG, $DB;

    $tmpstr = $tmp ? 'tmp' : '';
    $table = 'individualfeedback_completed';
    if ($tmp) {
        $table = 'indfeedback_completedtmp';
    }

    if (!$courseid) {
        if ($guestid) {
            $params = array('individualfeedback'=>$individualfeedbackid, 'guestid'=>$guestid);
            return $DB->get_record($table, $params);
        } else {
            $params = array('individualfeedback'=>$individualfeedbackid, 'userid'=>individualfeedback_hash_userid($USER->id));
            return $DB->get_record($table, $params);
        }
    }

    $params = array();

    if ($guestid) {
        $userselect = "AND fc.guestid = :guestid";
        $params['guestid'] = $guestid;
    } else {
        $userselect = "AND fc.userid = :userid";
        $params['userid'] = individualfeedback_hash_userid($USER->id);
    }
    //if courseid is set the individualfeedback is global.
    //there can be more than one completed on one individualfeedback
    $sql =  "SELECT DISTINCT fc.*
               FROM {individualfeedback_value{$tmpstr}} fv, {$table} fc
              WHERE fv.course_id = :courseid
                    AND fv.completed = fc.id
                    $userselect
                    AND fc.individualfeedback = :individualfeedbackid";
    $params['courseid']   = intval($courseid);
    $params['individualfeedbackid'] = $individualfeedbackid;

    if (!$sqlresult = $DB->get_records_sql($sql, $params)) {
        return false;
    }
    foreach ($sqlresult as $r) {
        return $DB->get_record($table, array('id'=>$r->id));
    }
}

/**
 * get the completeds depending on the given groupid.
 *
 * @global object
 * @global object
 * @param object $individualfeedback
 * @param int $groupid
 * @param int $courseid
 * @return mixed array of found completeds otherwise false
 */
function individualfeedback_get_completeds_group($individualfeedback, $groupid = false, $courseid = false) {
    global $CFG, $DB;

    if (intval($groupid) > 0) {
        $query = "SELECT fbc.*
                    FROM {individualfeedback_completed} fbc, {groups_members} gm
                   WHERE fbc.individualfeedback = ?
                         AND gm.groupid = ?
                         AND fbc.userid = gm.userid";
        if ($values = $DB->get_records_sql($query, array($individualfeedback->id, $groupid))) {
            return $values;
        } else {
            return false;
        }
    } else {
        if ($courseid) {
            $query = "SELECT DISTINCT fbc.*
                        FROM {individualfeedback_completed} fbc, {individualfeedback_value} fbv
                        WHERE fbc.id = fbv.completed
                            AND fbc.individualfeedback = ?
                            AND fbv.course_id = ?
                        ORDER BY random_response";
            if ($values = $DB->get_records_sql($query, array($individualfeedback->id, $courseid))) {
                return $values;
            } else {
                return false;
            }
        } else {
            if ($values = $DB->get_records('individualfeedback_completed', array('individualfeedback'=>$individualfeedback->id))) {
                return $values;
            } else {
                return false;
            }
        }
    }
}

/**
 * get the count of completeds depending on the given groupid.
 *
 * @global object
 * @global object
 * @param object $individualfeedback
 * @param int $groupid
 * @param int $courseid
 * @return mixed count of completeds or false
 */
function individualfeedback_get_completeds_group_count($individualfeedback, $groupid = false, $courseid = false) {
    global $CFG, $DB;

    if ($courseid > 0 AND !$groupid <= 0) {
        $sql = "SELECT id, COUNT(item) AS ci
                  FROM {individualfeedback_value}
                 WHERE course_id  = ?
              GROUP BY item ORDER BY ci DESC";
        if ($foundrecs = $DB->get_records_sql($sql, array($courseid))) {
            $foundrecs = array_values($foundrecs);
            return $foundrecs[0]->ci;
        }
        return false;
    }
    if ($values = individualfeedback_get_completeds_group($individualfeedback, $groupid)) {
        return count($values);
    } else {
        return false;
    }
}

/**
 * deletes all completed-recordsets from a individualfeedback.
 * all related data such as values also will be deleted
 *
 * @param stdClass|int $individualfeedback
 * @param stdClass|cm_info $cm
 * @param stdClass $course
 * @return void
 */
function individualfeedback_delete_all_completeds($individualfeedback, $cm = null, $course = null) {
    global $DB;

    if (is_int($individualfeedback)) {
        $individualfeedback = $DB->get_record('individualfeedback', array('id' => $individualfeedback));
    }

    if (!$completeds = $DB->get_records('individualfeedback_completed', array('individualfeedback' => $individualfeedback->id))) {
        return;
    }

    if (!$course && !($course = $DB->get_record('course', array('id' => $individualfeedback->course)))) {
        return false;
    }

    if (!$cm && !($cm = get_coursemodule_from_instance('individualfeedback', $individualfeedback->id))) {
        return false;
    }

    foreach ($completeds as $completed) {
        individualfeedback_delete_completed($completed, $individualfeedback, $cm, $course);
    }
}

/**
 * deletes a completed given by completedid.
 * all related data such values or tracking data also will be deleted
 *
 * @param int|stdClass $completed
 * @param stdClass $individualfeedback
 * @param stdClass|cm_info $cm
 * @param stdClass $course
 * @return boolean
 */
function individualfeedback_delete_completed($completed, $individualfeedback = null, $cm = null, $course = null) {
    global $DB, $CFG;
    require_once($CFG->libdir.'/completionlib.php');

    if (!isset($completed->id)) {
        if (!$completed = $DB->get_record('individualfeedback_completed', array('id' => $completed))) {
            return false;
        }
    }

    if (!$individualfeedback && !($individualfeedback = $DB->get_record('individualfeedback', array('id' => $completed->individualfeedback)))) {
        return false;
    }

    if (!$course && !($course = $DB->get_record('course', array('id' => $individualfeedback->course)))) {
        return false;
    }

    if (!$cm && !($cm = get_coursemodule_from_instance('individualfeedback', $individualfeedback->id))) {
        return false;
    }

    //first we delete all related values
    $DB->delete_records('individualfeedback_value', array('completed' => $completed->id));

    // Delete the completed record.
    $return = $DB->delete_records('individualfeedback_completed', array('id' => $completed->id));

    // Update completion state
    $completion = new completion_info($course);
    if ($completion->is_enabled($cm) && $cm->completion == COMPLETION_TRACKING_AUTOMATIC && $individualfeedback->completionsubmit) {
        $completion->update_state($cm, COMPLETION_INCOMPLETE, $completed->userid);
    }
    // Trigger event for the delete action we performed.
    $event = \mod_individualfeedback\event\response_deleted::create_from_record($completed, $cm, $individualfeedback);
    $event->trigger();

    return $return;
}

////////////////////////////////////////////////
////////////////////////////////////////////////
////////////////////////////////////////////////
//functions to handle sitecourse mapping
////////////////////////////////////////////////

/**
 * checks if the course and the individualfeedback is in the table indfeedback_sitecourse_map.
 *
 * @deprecated since 3.1
 * @param int $individualfeedbackid
 * @param int $courseid
 * @return int the count of records
 */
function individualfeedback_is_course_in_sitecourse_map($individualfeedbackid, $courseid) {
    debugging('Function individualfeedback_is_course_in_sitecourse_map() is deprecated because it was not used.',
            DEBUG_DEVELOPER);
    global $DB;
    $params = array('individualfeedbackid'=>$individualfeedbackid, 'courseid'=>$courseid);
    return $DB->count_records('indfeedback_sitecourse_map', $params);
}

/**
 * checks if the individualfeedback is in the table indfeedback_sitecourse_map.
 *
 * @deprecated since 3.1
 * @param int $individualfeedbackid
 * @return boolean
 */
function individualfeedback_is_individualfeedback_in_sitecourse_map($individualfeedbackid) {
    debugging('Function individualfeedback_is_individualfeedback_in_sitecourse_map() is deprecated because it was not used.',
            DEBUG_DEVELOPER);
    global $DB;
    return $DB->record_exists('indfeedback_sitecourse_map', array('individualfeedbackid'=>$individualfeedbackid));
}

/**
 * gets the individualfeedbacks from table indfeedback_sitecourse_map.
 * this is used to show the global individualfeedbacks on the individualfeedback block
 * all individualfeedbacks with the following criteria will be selected:<br />
 *
 * 1) all individualfeedbacks which id are listed together with the courseid in sitecoursemap and<br />
 * 2) all individualfeedbacks which not are listed in sitecoursemap
 *
 * @global object
 * @param int $courseid
 * @return array the individualfeedback-records
 */
function individualfeedback_get_individualfeedbacks_from_sitecourse_map($courseid) {
    global $DB;

    //first get all individualfeedbacks listed in sitecourse_map with named courseid
    $sql = "SELECT f.id AS id,
                   cm.id AS cmid,
                   f.name AS name,
                   f.timeopen AS timeopen,
                   f.timeclose AS timeclose
            FROM {individualfeedback} f, {course_modules} cm, {indfeedback_sitecourse_map} sm, {modules} m
            WHERE f.id = cm.instance
                   AND f.course = '".SITEID."'
                   AND m.id = cm.module
                   AND m.name = 'individualfeedback'
                   AND sm.courseid = ?
                   AND sm.individualfeedbackid = f.id";

    if (!$individualfeedbacks1 = $DB->get_records_sql($sql, array($courseid))) {
        $individualfeedbacks1 = array();
    }

    //second get all individualfeedbacks not listed in sitecourse_map
    $individualfeedbacks2 = array();
    $sql = "SELECT f.id AS id,
                   cm.id AS cmid,
                   f.name AS name,
                   f.timeopen AS timeopen,
                   f.timeclose AS timeclose
            FROM {individualfeedback} f, {course_modules} cm, {modules} m
            WHERE f.id = cm.instance
                   AND f.course = '".SITEID."'
                   AND m.id = cm.module
                   AND m.name = 'individualfeedback'";
    if (!$allindividualfeedbacks = $DB->get_records_sql($sql)) {
        $allindividualfeedbacks = array();
    }
    foreach ($allindividualfeedbacks as $a) {
        if (!$DB->record_exists('indfeedback_sitecourse_map', array('individualfeedbackid'=>$a->id))) {
            $individualfeedbacks2[] = $a;
        }
    }

    $individualfeedbacks = array_merge($individualfeedbacks1, $individualfeedbacks2);
    $modinfo = get_fast_modinfo(SITEID);
    return array_filter($individualfeedbacks, function($f) use ($modinfo) {
        return ($cm = $modinfo->get_cm($f->cmid)) && $cm->uservisible;
    });

}

/**
 * Gets the courses from table indfeedback_sitecourse_map
 *
 * @param int $individualfeedbackid
 * @return array the course-records
 */
function individualfeedback_get_courses_from_sitecourse_map($individualfeedbackid) {
    global $DB;

    $sql = "SELECT c.id, c.fullname, c.shortname
              FROM {indfeedback_sitecourse_map} f, {course} c
             WHERE c.id = f.courseid
                   AND f.individualfeedbackid = ?
          ORDER BY c.fullname";

    return $DB->get_records_sql($sql, array($individualfeedbackid));

}

/**
 * Updates the course mapping for the individualfeedback
 *
 * @param stdClass $individualfeedback
 * @param array $courses array of course ids
 */
function individualfeedback_update_sitecourse_map($individualfeedback, $courses) {
    global $DB;
    if (empty($courses)) {
        $courses = array();
    }
    $currentmapping = $DB->get_fieldset_select('indfeedback_sitecourse_map', 'courseid', 'individualfeedbackid=?', array($individualfeedback->id));
    foreach (array_diff($courses, $currentmapping) as $courseid) {
        $DB->insert_record('indfeedback_sitecourse_map', array('individualfeedbackid' => $individualfeedback->id, 'courseid' => $courseid));
    }
    foreach (array_diff($currentmapping, $courses) as $courseid) {
        $DB->delete_records('indfeedback_sitecourse_map', array('individualfeedbackid' => $individualfeedback->id, 'courseid' => $courseid));
    }
    // TODO MDL-53574 add events.
}

/**
 * removes non existing courses or individualfeedbacks from sitecourse_map.
 * it shouldn't be called all too often
 * a good place for it could be the mapcourse.php or unmapcourse.php
 *
 * @deprecated since 3.1
 * @global object
 * @return void
 */
function individualfeedback_clean_up_sitecourse_map() {
    global $DB;
    debugging('Function individualfeedback_clean_up_sitecourse_map() is deprecated because it was not used.',
            DEBUG_DEVELOPER);

    $maps = $DB->get_records('indfeedback_sitecourse_map');
    foreach ($maps as $map) {
        if (!$DB->get_record('course', array('id'=>$map->courseid))) {
            $params = array('courseid'=>$map->courseid, 'individualfeedbackid'=>$map->individualfeedbackid);
            $DB->delete_records('indfeedback_sitecourse_map', $params);
            continue;
        }
        if (!$DB->get_record('individualfeedback', array('id'=>$map->individualfeedbackid))) {
            $params = array('courseid'=>$map->courseid, 'individualfeedbackid'=>$map->individualfeedbackid);
            $DB->delete_records('indfeedback_sitecourse_map', $params);
            continue;
        }

    }
}

////////////////////////////////////////////////
////////////////////////////////////////////////
////////////////////////////////////////////////
//not relatable functions
////////////////////////////////////////////////

/**
 * prints the option items of a selection-input item (dropdownlist).
 * @deprecated since 3.1
 * @param int $startval the first value of the list
 * @param int $endval the last value of the list
 * @param int $selectval which item should be selected
 * @param int $interval the stepsize from the first to the last value
 * @return void
 */
function individualfeedback_print_numeric_option_list($startval, $endval, $selectval = '', $interval = 1) {
    debugging('Function individualfeedback_print_numeric_option_list() is deprecated because it was not used.',
            DEBUG_DEVELOPER);
    for ($i = $startval; $i <= $endval; $i += $interval) {
        if ($selectval == ($i)) {
            $selected = 'selected="selected"';
        } else {
            $selected = '';
        }
        echo '<option '.$selected.'>'.$i.'</option>';
    }
}

/**
 * sends an email to the teachers of the course where the given individualfeedback is placed.
 *
 * @global object
 * @global object
 * @uses INDIVIDUALFEEDBACK_ANONYMOUS_NO
 * @uses FORMAT_PLAIN
 * @param object $cm the coursemodule-record
 * @param object $individualfeedback
 * @param object $course
 * @param stdClass|int $user
 * @param stdClass $completed record from individualfeedback_completed if known
 * @return void
 */
function individualfeedback_send_email($cm, $individualfeedback, $course, $user, $completed = null) {
    global $CFG, $DB;

    if ($individualfeedback->email_notification == 0) {  // No need to do anything
        return;
    }

    if (is_int($user)) {
        $user = $DB->get_record('user', array('id' => $user));
    }

    if (isset($cm->groupmode) && empty($course->groupmodeforce)) {
        $groupmode =  $cm->groupmode;
    } else {
        $groupmode = $course->groupmode;
    }

    if ($groupmode == SEPARATEGROUPS) {
        $groups = $DB->get_records_sql_menu("SELECT g.name, g.id
                                               FROM {groups} g, {groups_members} m
                                              WHERE g.courseid = ?
                                                    AND g.id = m.groupid
                                                    AND m.userid = ?
                                           ORDER BY name ASC", array($course->id, $user->id));
        $groups = array_values($groups);

        $teachers = individualfeedback_get_receivemail_users($cm->id, $groups);
    } else {
        $teachers = individualfeedback_get_receivemail_users($cm->id);
    }

    if ($teachers) {

        $strindividualfeedbacks = get_string('modulenameplural', 'individualfeedback');
        $strindividualfeedback  = get_string('modulename', 'individualfeedback');

        if ($individualfeedback->anonymous == INDIVIDUALFEEDBACK_ANONYMOUS_NO) {
            $printusername = fullname($user);
        } else {
            $printusername = get_string('anonymous_user', 'individualfeedback');
        }

        foreach ($teachers as $teacher) {
            $info = new stdClass();
            $info->username = $printusername;
            $info->individualfeedback = format_string($individualfeedback->name, true);
            $info->url = $CFG->wwwroot.'/mod/individualfeedback/show_entries.php?'.
                            'id='.$cm->id.'&'.
                            'userid=' . $user->id;
            if ($completed) {
                $info->url .= '&showcompleted=' . $completed->id;
                if ($individualfeedback->course == SITEID) {
                    // Course where individualfeedback was completed (for site individualfeedbacks only).
                    $info->url .= '&courseid=' . $completed->courseid;
                }
            }

            $a = array('username' => $info->username, 'individualfeedbackname' => $individualfeedback->name);

            $postsubject = get_string('individualfeedbackcompleted', 'individualfeedback', $a);
            $posttext = individualfeedback_send_email_text($info, $course);

            if ($teacher->mailformat == 1) {
                $posthtml = individualfeedback_send_email_html($info, $course, $cm);
            } else {
                $posthtml = '';
            }

            if ($individualfeedback->anonymous == INDIVIDUALFEEDBACK_ANONYMOUS_NO) {
                $eventdata = new \core\message\message();
                $eventdata->courseid         = $course->id;
                $eventdata->name             = 'submission';
                $eventdata->component        = 'mod_individualfeedback';
                $eventdata->userfrom         = $user;
                $eventdata->userto           = $teacher;
                $eventdata->subject          = $postsubject;
                $eventdata->fullmessage      = $posttext;
                $eventdata->fullmessageformat = FORMAT_PLAIN;
                $eventdata->fullmessagehtml  = $posthtml;
                $eventdata->smallmessage     = '';
                $eventdata->courseid         = $course->id;
                $eventdata->contexturl       = $info->url;
                $eventdata->contexturlname   = $info->individualfeedback;
                message_send($eventdata);
            } else {
                $eventdata = new \core\message\message();
                $eventdata->courseid         = $course->id;
                $eventdata->name             = 'submission';
                $eventdata->component        = 'mod_individualfeedback';
                $eventdata->userfrom         = $teacher;
                $eventdata->userto           = $teacher;
                $eventdata->subject          = $postsubject;
                $eventdata->fullmessage      = $posttext;
                $eventdata->fullmessageformat = FORMAT_PLAIN;
                $eventdata->fullmessagehtml  = $posthtml;
                $eventdata->smallmessage     = '';
                $eventdata->courseid         = $course->id;
                $eventdata->contexturl       = $info->url;
                $eventdata->contexturlname   = $info->individualfeedback;
                message_send($eventdata);
            }
        }
    }
}

/**
 * sends an email to the teachers of the course where the given individualfeedback is placed.
 *
 * @global object
 * @uses FORMAT_PLAIN
 * @param object $cm the coursemodule-record
 * @param object $individualfeedback
 * @param object $course
 * @return void
 */
function individualfeedback_send_email_anonym($cm, $individualfeedback, $course) {
    global $CFG;

    if ($individualfeedback->email_notification == 0) { // No need to do anything
        return;
    }

    $teachers = individualfeedback_get_receivemail_users($cm->id);

    if ($teachers) {

        $strindividualfeedbacks = get_string('modulenameplural', 'individualfeedback');
        $strindividualfeedback  = get_string('modulename', 'individualfeedback');
        $printusername = get_string('anonymous_user', 'individualfeedback');

        foreach ($teachers as $teacher) {
            $info = new stdClass();
            $info->username = $printusername;
            $info->individualfeedback = format_string($individualfeedback->name, true);
            $info->url = $CFG->wwwroot.'/mod/individualfeedback/show_entries.php?id=' . $cm->id;

            $a = array('username' => $info->username, 'individualfeedbackname' => $individualfeedback->name);

            $postsubject = get_string('individualfeedbackcompleted', 'individualfeedback', $a);
            $posttext = individualfeedback_send_email_text($info, $course);

            if ($teacher->mailformat == 1) {
                $posthtml = individualfeedback_send_email_html($info, $course, $cm);
            } else {
                $posthtml = '';
            }

            $eventdata = new \core\message\message();
            $eventdata->courseid         = $course->id;
            $eventdata->name             = 'submission';
            $eventdata->component        = 'mod_individualfeedback';
            $eventdata->userfrom         = $teacher;
            $eventdata->userto           = $teacher;
            $eventdata->subject          = $postsubject;
            $eventdata->fullmessage      = $posttext;
            $eventdata->fullmessageformat = FORMAT_PLAIN;
            $eventdata->fullmessagehtml  = $posthtml;
            $eventdata->smallmessage     = '';
            $eventdata->courseid         = $course->id;
            $eventdata->contexturl       = $info->url;
            $eventdata->contexturlname   = $info->individualfeedback;
            message_send($eventdata);
        }
    }
}

/**
 * send the text-part of the email
 *
 * @param object $info includes some infos about the individualfeedback you want to send
 * @param object $course
 * @return string the text you want to post
 */
function individualfeedback_send_email_text($info, $course) {
    $coursecontext = context_course::instance($course->id);
    $courseshortname = format_string($course->shortname, true, array('context' => $coursecontext));
    $posttext  = $courseshortname.' -> '.get_string('modulenameplural', 'individualfeedback').' -> '.
                    $info->individualfeedback."\n";
    $posttext .= '---------------------------------------------------------------------'."\n";
    $posttext .= get_string("emailteachermail", "individualfeedback", $info)."\n";
    $posttext .= '---------------------------------------------------------------------'."\n";
    return $posttext;
}


/**
 * send the html-part of the email
 *
 * @global object
 * @param object $info includes some infos about the individualfeedback you want to send
 * @param object $course
 * @return string the text you want to post
 */
function individualfeedback_send_email_html($info, $course, $cm) {
    global $CFG;
    $coursecontext = context_course::instance($course->id);
    $courseshortname = format_string($course->shortname, true, array('context' => $coursecontext));
    $course_url = $CFG->wwwroot.'/course/view.php?id='.$course->id;
    $individualfeedback_all_url = $CFG->wwwroot.'/mod/individualfeedback/index.php?id='.$course->id;
    $individualfeedback_url = $CFG->wwwroot.'/mod/individualfeedback/view.php?id='.$cm->id;

    $posthtml = '<p><font face="sans-serif">'.
            '<a href="'.$course_url.'">'.$courseshortname.'</a> ->'.
            '<a href="'.$individualfeedback_all_url.'">'.get_string('modulenameplural', 'individualfeedback').'</a> ->'.
            '<a href="'.$individualfeedback_url.'">'.$info->individualfeedback.'</a></font></p>';
    $posthtml .= '<hr /><font face="sans-serif">';
    $posthtml .= '<p>'.get_string('emailteachermailhtml', 'individualfeedback', $info).'</p>';
    $posthtml .= '</font><hr />';
    return $posthtml;
}

/**
 * @param string $url
 * @return string
 */
function individualfeedback_encode_target_url($url) {
    if (strpos($url, '?')) {
        list($part1, $part2) = explode('?', $url, 2); //maximal 2 parts
        return $part1 . '?' . htmlentities($part2);
    } else {
        return $url;
    }
}

/**
 * Adds module specific settings to the settings block
 *
 * @param settings_navigation $settings The settings navigation object
 * @param navigation_node $individualfeedbacknode The node to add module settings to
 */
function individualfeedback_extend_settings_navigation(settings_navigation $settings,
                                             navigation_node $individualfeedbacknode) {

    global $PAGE;

    if (!$context = context_module::instance($PAGE->cm->id, IGNORE_MISSING)) {
        throw new \moodle_exception('badcontext');
    }

    if (has_capability('mod/individualfeedback:edititems', $context)) {
        $questionnode = $individualfeedbacknode->add(get_string('questions', 'individualfeedback'));

        $questionnode->add(get_string('edit_items', 'individualfeedback'),
                    new moodle_url('/mod/individualfeedback/edit.php',
                                    array('id' => $PAGE->cm->id,
                                          'do_show' => 'edit')));

        $questionnode->add(get_string('export_questions', 'individualfeedback'),
                    new moodle_url('/mod/individualfeedback/export.php',
                                    array('id' => $PAGE->cm->id,
                                          'action' => 'exportfile')));

        $questionnode->add(get_string('import_questions', 'individualfeedback'),
                    new moodle_url('/mod/individualfeedback/import.php',
                                    array('id' => $PAGE->cm->id)));

        $questionnode->add(get_string('templates', 'individualfeedback'),
                    new moodle_url('/mod/individualfeedback/edit.php',
                                    array('id' => $PAGE->cm->id,
                                          'do_show' => 'templates')));
    }

    if (has_capability('mod/individualfeedback:mapcourse', $context) && $PAGE->course->id == SITEID) {
        $individualfeedbacknode->add(get_string('mappedcourses', 'individualfeedback'),
                    new moodle_url('/mod/individualfeedback/mapcourse.php',
                                    array('id' => $PAGE->cm->id)));
    }

    if (has_capability('mod/individualfeedback:viewreports', $context)) {
        $individualfeedback = $PAGE->activityrecord;
        if ($individualfeedback->course == SITEID) {
            $individualfeedbacknode->add(get_string('analysis', 'individualfeedback'),
                    new moodle_url('/mod/individualfeedback/analysis_course.php',
                                    array('id' => $PAGE->cm->id)));
        } else {
            $individualfeedbacknode->add(get_string('analysis', 'individualfeedback'),
                    new moodle_url('/mod/individualfeedback/analysis.php',
                                    array('id' => $PAGE->cm->id)));
        }

        $individualfeedbacknode->add(get_string('show_entries', 'individualfeedback'),
                    new moodle_url('/mod/individualfeedback/show_entries.php',
                                    array('id' => $PAGE->cm->id)));

        if ($individualfeedback->anonymous == INDIVIDUALFEEDBACK_ANONYMOUS_NO AND $individualfeedback->course != SITEID) {
            $individualfeedbacknode->add(get_string('show_nonrespondents', 'individualfeedback'),
                        new moodle_url('/mod/individualfeedback/show_nonrespondents.php',
                                        array('id' => $PAGE->cm->id)));
        }
    }
}

function individualfeedback_init_individualfeedback_session() {
    //initialize the individualfeedback-Session - not nice at all!!
    global $SESSION;
    if (!empty($SESSION)) {
        if (!isset($SESSION->individualfeedback) OR !is_object($SESSION->individualfeedback)) {
            $SESSION->individualfeedback = new stdClass();
        }
    }
}

/**
 * Return a list of page types
 * @param string $pagetype current page type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 */
function individualfeedback_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $module_pagetype = array('mod-individualfeedback-*'=>get_string('page-mod-individualfeedback-x', 'individualfeedback'));
    return $module_pagetype;
}

/**
 * Move save the items of the given $individualfeedback in the order of $itemlist.
 * @param string $itemlist a comma separated list with item ids
 * @param stdClass $individualfeedback
 * @return bool true if success
 */
function individualfeedback_ajax_saveitemorder($itemlist, $individualfeedback) {
    global $DB;

    $result = true;
    $position = 0;
    foreach ($itemlist as $itemid) {
        $position++;
        $result = $result && $DB->set_field('individualfeedback_item',
                                            'position',
                                            $position,
                                            array('id'=>$itemid, 'individualfeedback'=>$individualfeedback->id));
    }
    return $result;
}

/**
 * Checks if current user is able to view individualfeedback on this course.
 *
 * @param stdClass $individualfeedback
 * @param context_module $context
 * @param int $courseid
 * @return bool
 */
function individualfeedback_can_view_analysis($individualfeedback, $context, $courseid = false) {
    if (has_capability('mod/individualfeedback:viewreports', $context)) {
        return true;
    }

    if (intval($individualfeedback->publish_stats) != 1 ||
            !has_capability('mod/individualfeedback:viewanalysepage', $context)) {
        return false;
    }

    if (!isloggedin() || isguestuser()) {
        // There is no tracking for the guests, assume that they can view analysis if condition above is satisfied.
        return $individualfeedback->course == SITEID;
    }

    return individualfeedback_is_already_submitted($individualfeedback->id, $courseid);
}

/**
 * Get icon mapping for font-awesome.
 */
function mod_individualfeedback_get_fontawesome_icon_map() {
    return [
        'mod_individualfeedback:required' => 'fa-exclamation-circle',
        'mod_individualfeedback:notrequired' => 'fa-question-circle-o',
    ];
}

/**
 * Check if the module has any update that affects the current user since a given time.
 *
 * @param  cm_info $cm course module data
 * @param  int $from the time to check updates from
 * @param  array $filter if we need to check only specific updates
 * @return stdClass an object with the different type of areas indicating if they were updated or not
 * @since Moodle 3.3
 */
function individualfeedback_check_updates_since(cm_info $cm, $from, $filter = array()) {
    global $DB, $USER, $CFG;

    $updates = course_check_module_updates_since($cm, $from, array(), $filter);

    // Check for new attempts.
    $updates->attemptsfinished = (object) array('updated' => false);
    $updates->attemptsunfinished = (object) array('updated' => false);
    $select = 'individualfeedback = ? AND userid = ? AND timemodified > ?';
    $params = array($cm->instance, individualfeedback_hash_userid($USER->id), $from);

    $attemptsfinished = $DB->get_records_select('individualfeedback_completed', $select, $params, '', 'id');
    if (!empty($attemptsfinished)) {
        $updates->attemptsfinished->updated = true;
        $updates->attemptsfinished->itemids = array_keys($attemptsfinished);
    }
    $attemptsunfinished = $DB->get_records_select('indfeedback_completedtmp', $select, $params, '', 'id');
    if (!empty($attemptsunfinished)) {
        $updates->attemptsunfinished->updated = true;
        $updates->attemptsunfinished->itemids = array_keys($attemptsunfinished);
    }

    // Now, teachers should see other students updates.
    if (has_capability('mod/individualfeedback:viewreports', $cm->context)) {
        $select = 'individualfeedback = ? AND timemodified > ?';
        $params = array($cm->instance, $from);

        if (groups_get_activity_groupmode($cm) == SEPARATEGROUPS) {
            $groupusers = array_keys(groups_get_activity_shared_group_members($cm));
            if (empty($groupusers)) {
                return $updates;
            }
            list($insql, $inparams) = $DB->get_in_or_equal($groupusers);
            $select .= ' AND userid ' . $insql;
            $params = array_merge($params, $inparams);
        }

        $updates->userattemptsfinished = (object) array('updated' => false);
        $attemptsfinished = $DB->get_records_select('individualfeedback_completed', $select, $params, '', 'id');
        if (!empty($attemptsfinished)) {
            $updates->userattemptsfinished->updated = true;
            $updates->userattemptsfinished->itemids = array_keys($attemptsfinished);
        }

        $updates->userattemptsunfinished = (object) array('updated' => false);
        $attemptsunfinished = $DB->get_records_select('indfeedback_completedtmp', $select, $params, '', 'id');
        if (!empty($attemptsunfinished)) {
            $updates->userattemptsunfinished->updated = true;
            $updates->userattemptsunfinished->itemids = array_keys($attemptsunfinished);
        }
    }

    return $updates;
}

/**
 * The event is only visible anywhere if the user can submit individualfeedback.
 *
 * @param calendar_event $event
 * @return bool Returns true if the event is visible to the current user, false otherwise.
 */
function mod_individualfeedback_core_calendar_is_event_visible(calendar_event $event) {
    global $DB;

    $cm = get_fast_modinfo($event->courseid)->instances['individualfeedback'][$event->instance];
    $individualfeedbackcompletion = new mod_individualfeedback_completion(null, $cm, 0);

    // The event is only visible if the user can submit it.
    return $individualfeedbackcompletion->can_complete();
}

/**
 * This function receives a calendar event and returns the action associated with it, or null if there is none.
 *
 * This is used by block_myoverview in order to display the event appropriately. If null is returned then the event
 * is not displayed on the block.
 *
 * @param calendar_event $event
 * @param \core_calendar\action_factory $factory
 * @return \core_calendar\local\event\entities\action_interface|null
 */
function mod_individualfeedback_core_calendar_provide_event_action(calendar_event $event,
                                                         \core_calendar\action_factory $factory) {

    $cm = get_fast_modinfo($event->courseid)->instances['individualfeedback'][$event->instance];
    $individualfeedbackcompletion = new mod_individualfeedback_completion(null, $cm, 0);

    if (!empty($cm->customdata['timeclose']) && $cm->customdata['timeclose'] < time()) {
        // individualfeedback is already closed, do not display it even if it was never submitted.
        return null;
    }

    // The individualfeedback is actionable if it does not have timeopen or timeopen is in the past.
    $actionable = $individualfeedbackcompletion->is_open();

    if ($actionable && $individualfeedbackcompletion->is_already_submitted()) {
        // There is no need to display anything if the user has already submitted the individualfeedback.
        return null;
    }

    return $factory->create_instance(
        get_string('answerquestions', 'individualfeedback'),
        new \moodle_url('/mod/individualfeedback/view.php', ['id' => $cm->id]),
        1,
        $actionable
    );
}

/**
 * Add a get_coursemodule_info function in case any individualfeedback type wants to add 'extra' information
 * for the course (see resource).
 *
 * Given a course_module object, this function returns any "extra" information that may be needed
 * when printing this activity in a course listing.  See get_array_of_activities() in course/lib.php.
 *
 * @param stdClass $coursemodule The coursemodule object (record).
 * @return cached_cm_info An object on information that the courses
 *                        will know about (most noticeably, an icon).
 */
function individualfeedback_get_coursemodule_info($coursemodule) {
    global $DB;

    $dbparams = ['id' => $coursemodule->instance];
    $fields = 'id, name, intro, introformat, completionsubmit, timeopen, timeclose, anonymous';
    if (!$individualfeedback = $DB->get_record('individualfeedback', $dbparams, $fields)) {
        return false;
    }

    $result = new cached_cm_info();
    $result->name = $individualfeedback->name;

    if ($coursemodule->showdescription) {
        // Convert intro to html. Do not filter cached version, filters run at display time.
        $result->content = format_module_intro('individualfeedback', $individualfeedback, $coursemodule->id, false);
    }

    // Populate the custom completion rules as key => value pairs, but only if the completion mode is 'automatic'.
    if ($coursemodule->completion == COMPLETION_TRACKING_AUTOMATIC) {
        $result->customdata['customcompletionrules']['completionsubmit'] = $individualfeedback->completionsubmit;
    }
    // Populate some other values that can be used in calendar or on dashboard.
    if ($individualfeedback->timeopen) {
        $result->customdata['timeopen'] = $individualfeedback->timeopen;
    }
    if ($individualfeedback->timeclose) {
        $result->customdata['timeclose'] = $individualfeedback->timeclose;
    }
    if ($individualfeedback->anonymous) {
        $result->customdata['anonymous'] = $individualfeedback->anonymous;
    }

    return $result;
}

/**
 * Callback which returns human-readable strings describing the active completion custom rules for the module instance.
 *
 * @param cm_info|stdClass $cm object with fields ->completion and ->customdata['customcompletionrules']
 * @return array $descriptions the array of descriptions for the custom rules.
 */
function mod_individualfeedback_get_completion_active_rule_descriptions($cm) {
    // Values will be present in cm_info, and we assume these are up to date.
    if (empty($cm->customdata['customcompletionrules'])
        || $cm->completion != COMPLETION_TRACKING_AUTOMATIC) {
        return [];
    }

    $descriptions = [];
    foreach ($cm->customdata['customcompletionrules'] as $key => $val) {
        switch ($key) {
            case 'completionsubmit':
                if (!empty($val)) {
                    $descriptions[] = get_string('completionsubmit', 'individualfeedback');
                }
                break;
            default:
                break;
        }
    }
    return $descriptions;
}

function individualfeedback_hash_userid($userid) {
    $salt = 'IeJ8GI6CD06UDU0y3lUVMQ8D7slxBlZm0LVRZRZV';
    return sha1($salt . $userid);
}

function individualfeedback_get_statistic_question_types() {
    return array('multichoice', 'fourlevelapproval', 'fourlevelfrequency', 'fivelevelapproval');
}

function individualfeedback_get_linkedid($individualfeedbackid) {
    global $DB;

    return $DB->get_field('individualfeedback_linked', 'linkedid', array('individualfeedbackid' => $individualfeedbackid));
}

function individualfeedback_create_linked_record($oldid, $newid) {
    global $DB;

    $linkedid = individualfeedback_get_linkedid($oldid);

    // No linked instances yet.
    if (!$linkedid) {
        $sql = "SELECT MAX(linkedid) FROM {individualfeedback_linked}";
        if (!$highestid = $DB->get_field_sql($sql)) {
            $highestid = 0;
        }

        // Raise the highestid.
        $highestid++;

        $record = new stdClass();
        $record->linkedid = $highestid;
        $record->individualfeedbackid = $oldid;
        $DB->insert_record('individualfeedback_linked', $record);

        $record = new stdClass();
        $record->linkedid = $highestid;
        $record->individualfeedbackid = $newid;
        $DB->insert_record('individualfeedback_linked', $record);
    } else {
        $record = new stdClass();
        $record->linkedid = $linkedid;
        $record->individualfeedbackid = $newid;
        $DB->insert_record('individualfeedback_linked', $record);
    }
}

function individualfeedback_check_linked_questions($individualfeedbackid) {
    global $DB;

    $allfeedbacks = individualfeedback_get_linked_individualfeedbacks($individualfeedbackid);
    if (count($allfeedbacks) < 2) {
        return false;
    }

    $countitems = 0;
    $firsttime = true;
    foreach ($allfeedbacks as $feedback) {
        $items = $DB->get_records('individualfeedback_item', array('individualfeedback' => $feedback->id));
        $allfeedbacks[$feedback->id]->items = $items;
        if (!$firsttime) {
            if ($countitems != count($items)) {
                return false;
            }
        }

        $firsttime = false;
        $countitems = count($items);
    }

    $base = reset($allfeedbacks);
    $baseitemkeys = array_keys($base->items);

    $firsttime = true;
    $checkfields = array('name', 'label', 'typ', 'position');
    foreach ($allfeedbacks as $feedback) {
        // Skip the first run, because you don't need to compare with itself.
        if ($firsttime) {
            $firsttime = false;
            continue;
        }

        $counter = 0;
        foreach ($feedback->items as $key => $item) {
            $checkitemkey = $baseitemkeys[$counter];
            $checkitem = $base->items[$checkitemkey];
            foreach ($checkfields as $field) {
                if ($item->$field != $checkitem->$field) {
                    return false;
                }
            }

            $counter++;
        }
    }

    return true;
}

function individualfeedback_get_linked_individualfeedbacks($individualfeedbackid) {
    global $DB;

    if (!$linkedid = individualfeedback_get_linkedid($individualfeedbackid)) {
        return array();
    }

    $sql = "SELECT ifb.*
    FROM {individualfeedback} ifb
    JOIN {individualfeedback_linked} ifbl ON ifb.id = ifbl.individualfeedbackid
    WHERE ifbl.linkedid = :linkedid
    ORDER BY ifb.timemodified DESC";

    return $DB->get_records_sql($sql, array('linkedid' => $linkedid));
}