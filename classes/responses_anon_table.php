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
 * Contains class mod_individualfeedback_responses_anon_table
 *
 * @package   mod_individualfeedback
 * @copyright 2016 Marina Glancy
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Class mod_individualfeedback_responses_anon_table
 *
 * @package   mod_individualfeedback
 * @copyright 2016 Marina Glancy
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_individualfeedback_responses_anon_table extends mod_individualfeedback_responses_table {

    /** @var string */
    protected $showallparamname = 'ashowall';

    /** @var string */
    protected $downloadparamname = 'adownload';

    /**
     * Initialises table
     * @param int $group retrieve only users from this group (optional)
     */
    public function init($group = 0) {

        $cm = $this->individualfeedbackstructure->get_cm();
        $this->uniqueid = 'individualfeedback-showentry-anon-list-' . $cm->instance;

        // There potentially can be both tables with anonymouns and non-anonymous responses on
        // the same page (for example when individualfeedback anonymity was changed after some people
        // already responded). In this case we need to distinguish tables' pagination parameters.
        $this->request[TABLE_VAR_PAGE] = 'apage';

        $tablecolumns = ['random_response'];
        $tableheaders = [get_string('response_nr', 'individualfeedback')];

        if ($this->individualfeedbackstructure->get_individualfeedback()->course == SITEID && !$this->individualfeedbackstructure->get_courseid()) {
            $tablecolumns[] = 'courseid';
            $tableheaders[] = get_string('course');
        }

        $this->define_columns($tablecolumns);
        $this->define_headers($tableheaders);

        $this->sortable(true, 'random_response');
        $this->collapsible(true);
        $this->set_attribute('id', 'showentryanontable');

        $params = ['instance' => $cm->instance,
            'anon' => INDIVIDUALFEEDBACK_ANONYMOUS_YES,
            'courseid' => $this->individualfeedbackstructure->get_courseid()];

        $fields = 'c.id, c.random_response, c.courseid, c.selfassessment';
        $from = '{individualfeedback_completed} c';
        $where = 'c.anonymous_response = :anon AND c.individualfeedback = :instance';
        if ($this->individualfeedbackstructure->get_courseid()) {
            $where .= ' AND c.courseid = :courseid';
        }

        $group = (empty($group)) ? groups_get_activity_group($this->individualfeedbackstructure->get_cm(), true) : $group;
        if ($group) {
            // Select groupmember by hashed userids.
            $this->add_groupmember_where_by_hashedids($group, $where, $params);
        }
        $this->set_sql($fields, $from, $where, $params);
        $this->set_count_sql("SELECT COUNT(c.id) FROM $from WHERE $where", $params);
    }

    /**
     * Returns a link for viewing a single response
     * @param stdClass $row
     * @return \moodle_url
     */
    protected function get_link_single_entry($row) {
        return new moodle_url($this->baseurl, ['showcompleted' => $row->id]);
    }

    /**
     * Prepares column reponse for display
     * @param stdClass $row
     * @return string
     */
    public function col_random_response($row) {
        $addrow = '';
        if (!empty($row->selfassessment)) {
            $addrow = " " . html_writer::tag('span', '*', array('title' => get_string('selfassessment', 'mod_individualfeedback')));
        }

        if ($this->is_downloading()) {
            return $row->random_response . strip_tags($addrow);
        } else {
            return html_writer::link($this->get_link_single_entry($row),
                    get_string('response_nr', 'individualfeedback').': '. $row->random_response . $addrow);
        }
    }

    /**
     * Add data for the external structure that will be returned.
     *
     * @param stdClass $row a database query record row
     * @since Moodle 3.3
     */
    protected function add_data_for_external($row) {
        $this->dataforexternal[] = [
            'id' => $row->id,
            'courseid' => $row->courseid,
            'number' => $row->random_response,
            'responses' => $this->get_responses_for_external($row)
        ];
    }
}
