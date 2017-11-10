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

defined('MOODLE_INTERNAL') OR die('not allowed');
require_once($CFG->dirroot.'/mod/individualfeedback/item/individualfeedback_item_class.php');

class individualfeedback_item_questiongroup extends individualfeedback_item_base {
    protected $type = "questiongroup";

    public function build_editform($item, $individualfeedback, $cm) {
        global $DB, $CFG;
        require_once('questiongroup_form.php');

        //get the lastposition number of the individualfeedback_items
        $position = $item->position;
        $lastposition = $DB->count_records('individualfeedback_item', array('individualfeedback'=>$individualfeedback->id));
        if ($position == -1) {
            $i_formselect_last = $lastposition + 1;
            $i_formselect_value = $lastposition + 1;
            $item->position = $lastposition + 1;
        } else {
            $i_formselect_last = $lastposition;
            $i_formselect_value = $item->position;
        }
        //the elements for position dropdownlist
        $positionlist = array_slice(range(0, $i_formselect_last), 1, $i_formselect_last, true);

        $item->presentation = '';

        //all items for dependitem
        $individualfeedbackitems = individualfeedback_get_depend_candidates_for_item($individualfeedback, $item);
        $commonparams = array('cmid' => $cm->id,
                             'id' => isset($item->id) ? $item->id : null,
                             'typ' => $item->typ,
                             'items' => $individualfeedbackitems,
                             'individualfeedback' => $individualfeedback->id);

        //build the form
        $customdata = array('item' => $item,
                            'common' => $commonparams,
                            'positionlist' => $positionlist,
                            'position' => $position);

        $this->item_form = new individualfeedback_questiongroup_form('edit_item.php', $customdata);
    }

    public function save_item() {
        global $DB;

        if (!$this->get_data()) {
            return false;
        }
        $item = $this->item;

        if (isset($item->clone_item) AND $item->clone_item) {
            $item->id = ''; //to clone this item
            $item->position++;
        }

        $item->hasvalue = $this->get_hasvalue();
        if (!$item->id) {
            $item->id = $DB->insert_record('individualfeedback_item', $item);
        } else {
            $DB->update_record('individualfeedback_item', $item);
        }
        
        $newitem = $DB->get_record('individualfeedback_item', array('id'=>$item->id));

        // Also create a end of the group so you can actually determine what the group is.

        $enditem = new stdClass();
        $enditem->individualfeedback = $item->individualfeedback;
        $enditem->template = 0;
        $enditem->name = '';
        $enditem->label = '';
        $enditem->presentation = '';
        $enditem->typ = 'questiongroupend';
        $enditem->hasvalue = 0;
        $enditem->position = $newitem->position + 1;
        $enditem->required = 0;
        $enditem->dependitem = $newitem->id;
        $enditem->dependvalue = '';
        $enditem->options = '';

        $enditem->id = $DB->insert_record('individualfeedback_item', $enditem);
        
        return $newitem;
    }

    public function get_printval($item, $value) {

        if (!isset($value->name)) {
            return '';
        }
        return $value->name;
    }

    public function print_analysed($item, $itemnr = '', $groupid = false, $courseid = false) {
        echo html_writer::start_tag('div', array('class' => 'questiongroup_analysed', 'id' => 'questiongroup_' . $item->id));
        echo html_writer::tag('div', $item->name);
    }

    public function excelprint_item(&$worksheet, $row_offset,
                             $xls_formats, $item,
                             $groupid, $courseid = false) {

        $worksheet->write_string($row_offset, 0, $item->name, $xls_formats->head2);
        $row_offset++;
        return $row_offset;
    }

    /**
     * Adds an input element to the complete form
     *
     * @param stdClass $item
     * @param mod_individualfeedback_complete_form $form
     */
    public function complete_form_element($item, $form) {
        global $OUTPUT, $PAGE;

        $form->add_form_element($item, ['html', html_writer::start_tag('div', ['class' => 'individualfeedback_questiongroup_start questiongroupmoveitem', 'id' => 'questiongroup_' . $item->id])]);
        if ($PAGE->url->get_param('do_show')) {
            $moveicon = html_writer::div($OUTPUT->pix_icon('i/move_2d', get_string('move_questiongroup', 'individualfeedback')), 'float-left drag-handle movequestiongroup');
            $form->add_form_element($item, ['html', $moveicon]);
        }
        $name = $this->get_display_name($item);
        $form->add_form_element($item,
            ['static',
                $item->typ.'_'.$item->id,
                '',
                html_writer::tag('span', $name, ['class' => 'individualfeedback_questiongroup', 'id' => 'individualfeedback_item_' . $item->id])
            ]);
    }

    /**
     * Converts the value from complete_form data to the string value that is stored in the db.
     * @param mixed $value element from mod_individualfeedback_complete_form::get_data() with the name $item->typ.'_'.$item->id
     * @return string
     */
    public function create_value($value) {
        return s($value);
    }

    /**
     * Return the analysis data ready for external functions.
     *
     * @param stdClass $item     the item (question) information
     * @param int      $groupid  the group id to filter data (optional)
     * @param int      $courseid the course id (optional)
     * @return array an array of data with non scalar types json encoded
     * @since  Moodle 3.3
     */
    public function get_analysed_for_external($item, $groupid = false, $courseid = false) {

        $externaldata = array();
        $data = $this->get_analysed($item, $groupid, $courseid);

        if (is_array($data->data)) {
            return $data->data; // No need to json, scalar type.
        }
        return $externaldata;
    }

    /**
     * Returns the list of actions allowed on this item in the edit mode
     *
     * @param stdClass $item
     * @param stdClass $individualfeedback
     * @param cm_info $cm
     * @return action_menu_link[]
     */
    public function edit_actions($item, $individualfeedback, $cm) {
        $actions = array();

        $strupdate = get_string('edit_questiongroup', 'individualfeedback');
        $actions['update'] = new action_menu_link_secondary(
            new moodle_url('/mod/individualfeedback/edit_item.php', array('id' => $item->id)),
            new pix_icon('t/edit', $strupdate, 'moodle', array('class' => 'iconsmall', 'title' => '')),
            $strupdate,
            array('class' => 'editing_update', 'data-action' => 'update')
        );

        $strdelete = get_string('delete_questiongroup', 'individualfeedback');
        $actions['delete'] = new action_menu_link_secondary(
            new moodle_url('/mod/individualfeedback/edit.php', array('id' => $cm->id, 'deleteitem' => $item->id, 'sesskey' => sesskey())),
            new pix_icon('t/delete', $strdelete, 'moodle', array('class' => 'iconsmall', 'title' => '')),
            $strdelete,
            array('class' => 'editing_delete questiongroup', 'data-action' => 'delete')
        );

        return $actions;
    }

    /**
     * Wether this item type has a value that is expected from the user and saved in the stored values.
     * @return int
     */
    public function get_hasvalue() {
        return 0;
    }
}
