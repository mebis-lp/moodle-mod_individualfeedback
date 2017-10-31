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
 * Contains class mod_individualfeedback\output\summary
 *
 * @package   mod_individualfeedback
 * @copyright 2016 Marina Glancy
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_individualfeedback\output;

use renderable;
use templatable;
use renderer_base;
use stdClass;
use moodle_url;
use mod_individualfeedback_structure;

/**
 * Class to help display individualfeedback summary
 *
 * @package   mod_individualfeedback
 * @copyright 2016 Marina Glancy
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class summary implements renderable, templatable {

    /** @var mod_individualfeedback_structure */
    protected $individualfeedbackstructure;

    /** @var int */
    protected $mygroupid;

    /** @var bool  */
    protected $extradetails;

    /**
     * Constructor.
     *
     * @param mod_individualfeedback_structure $individualfeedbackstructure
     * @param int $mygroupid currently selected group
     * @param bool $extradetails display additional details (time open, time closed)
     */
    public function __construct($individualfeedbackstructure, $mygroupid = false, $extradetails = false) {
        $this->individualfeedbackstructure = $individualfeedbackstructure;
        $this->mygroupid = $mygroupid;
        $this->extradetails = $extradetails;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        $r = new stdClass();
        $r->completedcount = $this->individualfeedbackstructure->count_completed_responses($this->mygroupid);
        $r->itemscount = count($this->individualfeedbackstructure->get_items(true));
        if ($this->extradetails && ($timeopen = $this->individualfeedbackstructure->get_individualfeedback()->timeopen)) {
            $r->timeopen = userdate($timeopen);
        }
        if ($this->extradetails && ($timeclose = $this->individualfeedbackstructure->get_individualfeedback()->timeclose)) {
            $r->timeclose = userdate($timeclose);
        }

        return $r;
    }
}
