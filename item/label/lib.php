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
require_once($CFG->libdir.'/formslib.php');

class individualfeedback_item_label extends individualfeedback_item_base {
    protected $type = "label";
    private $presentationoptions = null;
    private $context;

    /**
     * Constructor
     */
    public function __construct() {
        $this->presentationoptions = array('maxfiles' => EDITOR_UNLIMITED_FILES,
                                           'trusttext'=>true);

    }

    public function build_editform($item, $individualfeedback, $cm) {
        global $DB, $CFG;
        require_once('label_form.php');

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

        //all items for dependitem
        $individualfeedbackitems = individualfeedback_get_depend_candidates_for_item($individualfeedback, $item);
        $commonparams = array('cmid'=>$cm->id,
                             'id'=>isset($item->id) ? $item->id : null,
                             'typ'=>$item->typ,
                             'items'=>$individualfeedbackitems,
                             'individualfeedback'=>$individualfeedback->id);

        $this->context = context_module::instance($cm->id);

        //preparing the editor for new file-api
        $item->presentationformat = FORMAT_HTML;
        $item->presentationtrust = 1;

        // Append editor context to presentation options, giving preference to existing context.
        $this->presentationoptions = array_merge(array('context' => $this->context),
                                                 $this->presentationoptions);

        $item = file_prepare_standard_editor($item,
                                            'presentation', //name of the form element
                                            $this->presentationoptions,
                                            $this->context,
                                            'mod_individualfeedback',
                                            'item', //the filearea
                                            $item->id);

        //build the form
        $customdata = array('item' => $item,
                            'common' => $commonparams,
                            'positionlist' => $positionlist,
                            'position' => $position,
                            'presentationoptions' => $this->presentationoptions);

        $this->item_form = new individualfeedback_label_form('edit_item.php', $customdata);
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

        $item->presentation = '';

        $item->hasvalue = $this->get_hasvalue();
        if (!$item->id) {
            $item->id = $DB->insert_record('individualfeedback_item', $item);
        } else {
            $DB->update_record('individualfeedback_item', $item);
        }

        $item = file_postupdate_standard_editor($item,
                                                'presentation',
                                                $this->presentationoptions,
                                                $this->context,
                                                'mod_individualfeedback',
                                                'item',
                                                $item->id);

        $DB->update_record('individualfeedback_item', $item);

        return $DB->get_record('individualfeedback_item', array('id'=>$item->id));
    }

    /**
     * prepares the item for output or export to file
     * @param stdClass $item
     * @return string
     */
    private function print_item($item) {
        global $DB, $CFG;

        require_once($CFG->libdir . '/filelib.php');

        //is the item a template?
        if (!$item->individualfeedback AND $item->template) {
            $template = $DB->get_record('individualfeedback_template', array('id'=>$item->template));
            if ($template->ispublic) {
                $context = context_system::instance();
            } else {
                $context = context_course::instance($template->course);
            }
            $filearea = 'template';
        } else {
            $cm = get_coursemodule_from_instance('individualfeedback', $item->individualfeedback);
            $context = context_module::instance($cm->id);
            $filearea = 'item';
        }

        $item->presentationformat = FORMAT_HTML;
        $item->presentationtrust = 1;

        $output = file_rewrite_pluginfile_urls($item->presentation,
                                               'pluginfile.php',
                                               $context->id,
                                               'mod_individualfeedback',
                                               $filearea,
                                               $item->id);

        $formatoptions = array('overflowdiv'=>true, 'trusted'=>$CFG->enabletrusttext);
        echo format_text($output, FORMAT_HTML, $formatoptions);
    }

    /**
     * @param stdClass $item
     * @param bool|true $withpostfix
     * @return string
     */
    public function get_display_name($item, $withpostfix = true) {
        return '';
    }

    /**
     * Adds an input element to the complete form
     *
     * @param stdClass $item
     * @param mod_individualfeedback_complete_form $form
     */
    public function complete_form_element($item, $form) {
        global $DB;
        if (!$item->individualfeedback AND $item->template) {
            // This is a template.
            $template = $DB->get_record('individualfeedback_template', array('id' => $item->template));
            if ($template->ispublic) {
                $context = context_system::instance();
            } else {
                $context = context_course::instance($template->course);
            }
            $filearea = 'template';
        } else {
            // This is a question in the current individualfeedback.
            $context = $form->get_cm()->context;
            $filearea = 'item';
        }
        $output = file_rewrite_pluginfile_urls($item->presentation, 'pluginfile.php',
                $context->id, 'mod_individualfeedback', $filearea, $item->id);
        $formatoptions = array('overflowdiv' => true, 'noclean' => true);
        $output = format_text($output, FORMAT_HTML, $formatoptions);
        $output = html_writer::div($output, '', ['id' => 'individualfeedback_item_' . $item->id]);

        $inputname = $item->typ . '_' . $item->id;

        $name = $this->get_display_name($item);
        $form->add_form_element($item, ['static', $inputname, $name, $output], false, false);
    }

    public function compare_value($item, $dbvalue, $dependvalue) {
        return false;
    }

    public function postupdate($item) {
        global $DB;

        $context = context_module::instance($item->cmid);
        $item = file_postupdate_standard_editor($item,
                                                'presentation',
                                                $this->presentationoptions,
                                                $context,
                                                'mod_individualfeedback',
                                                'item',
                                                $item->id);

        $DB->update_record('individualfeedback_item', $item);
        return $item->id;
    }

    public function get_hasvalue() {
        return 0;
    }

    public function can_switch_require() {
        return false;
    }

    public function excelprint_item(&$worksheet,
                             $row_offset,
                             $xls_formats,
                             $item,
                             $groupid,
                             $courseid = false) {
    }

    public function print_analysed($item, $itemnr = '', $groupid = false, $courseid = false) {
    }
    public function get_printval($item, $value) {
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
        return [];
    }
}
