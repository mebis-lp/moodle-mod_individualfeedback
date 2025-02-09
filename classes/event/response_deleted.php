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
 * The mod_individualfeedback response deleted event.
 *
 * @package    mod_individualfeedback
 * @copyright  2013 Ankit Agarwal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */

namespace mod_individualfeedback\event;
defined('MOODLE_INTERNAL') || die();

/**
 * The mod_individualfeedback response deleted event class.
 *
 * This event is triggered when a individualfeedback response is deleted.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - int anonymous: if individualfeedback is anonymous.
 *      - int cmid: course module id.
 *      - int instanceid: id of instance.
 * }
 *
 * @package    mod_individualfeedback
 * @since      Moodle 2.6
 * @copyright  2013 Ankit Agarwal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */
class response_deleted extends \core\event\base {

    /**
     * Set basic properties for the event.
     */
    protected function init() {
        $this->data['objecttable'] = 'individualfeedback_completed';
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    /**
     * Creates an instance from the record from db table individualfeedback_completed
     *
     * @param stdClass $completed
     * @param stdClass|cm_info $cm
     * @param stdClass $individualfeedback
     * @return self
     */
    public static function create_from_record($completed, $cm, $individualfeedback) {
        $eventdata = array(
            'relateduserid' => 0,
            'objectid' => $completed->id,
            'courseid' => $cm->course,
            'context' => \context_module::instance($cm->id),
            'anonymous' => ($completed->anonymous_response == INDIVIDUALFEEDBACK_ANONYMOUS_YES),
            // Anonymize user in events.
            'userid' => 0,
            'other' => array(
                'cmid' => $cm->id,
                'instanceid' => $individualfeedback->id,
                'anonymous' => $completed->anonymous_response) // Deprecated.
        );

        if (!$completed->anonymous_response) {
            $eventdata['relateduserid'] = $completed->userid;
        }

        $event = self::create($eventdata);

        $event->add_record_snapshot('individualfeedback_completed', $completed);
        $event->add_record_snapshot('individualfeedback', $individualfeedback);
        return $event;
    }

    /**
     * Returns localised general event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventresponsedeleted', 'mod_individualfeedback');
    }

    /**
     * Returns non-localised event description with id's for admin use only.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' deleted the individualfeedback for the user with id '$this->relateduserid' " .
            "for the individualfeedback activity with course module id '$this->contextinstanceid'.";
    }

    /**
     * Define whether a user can view the event or not. Make sure no one except admin can see details of an anonymous response.
     *
     * @deprecated since 2.7
     *
     * @param int|\stdClass $userorid ID of the user.
     * @return bool True if the user can view the event, false otherwise.
     */
    public function can_view($userorid = null) {
        global $USER;
        debugging('can_view() method is deprecated, use anonymous flag instead if necessary.', DEBUG_DEVELOPER);

        if (empty($userorid)) {
            $userorid = $USER;
        }
        if ($this->anonymous) {
            return is_siteadmin($userorid);
        } else {
            return has_capability('mod/individualfeedback:viewreports', $this->context, $userorid);
        }
    }

    /**
     * Custom validations
     *
     * @throws \coding_exception in case of any problems.
     */
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }
        if (!isset($this->other['anonymous'])) {
            throw new \coding_exception('The \'anonymous\' value must be set in other.');
        }
        if (!isset($this->other['cmid'])) {
            throw new \coding_exception('The \'cmid\' value must be set in other.');
        }
        if (!isset($this->other['instanceid'])) {
            throw new \coding_exception('The \'instanceid\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'individualfeedback_completed', 'restore' => 'individualfeedback_completed');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['cmid'] = array('db' => 'course_modules', 'restore' => 'course_module');
        $othermapped['instanceid'] = array('db' => 'individualfeedback', 'restore' => 'individualfeedback');

        return $othermapped;
    }
}

