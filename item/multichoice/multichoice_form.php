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

require_once($CFG->dirroot.'/mod/individualfeedback/item/individualfeedback_item_form_class.php');

class individualfeedback_multichoice_form extends individualfeedback_item_form {
    protected $type = "multichoice";

    public function definition() {
        $item = $this->_customdata['item'];
        $common = $this->_customdata['common'];
        $positionlist = $this->_customdata['positionlist'];
        $position = $this->_customdata['position'];

        $mform =& $this->_form;

        $mform->addElement('header', 'general', get_string($this->type, 'individualfeedback'));

        //$mform->addElement('advcheckbox', 'required', get_string('required', 'individualfeedback'), '' , null , array(0, 1));
        $mform->addElement('hidden', 'required', 0);
        $mform->setType('required', PARAM_INT);

        $mform->addElement('text',
                            'name',
                            get_string('item_name', 'individualfeedback'),
                            array('size' => individualfeedback_ITEM_NAME_TEXTBOX_SIZE,
                                  'maxlength' => 255));

        $mform->addElement('text',
                            'label',
                            get_string('item_label', 'individualfeedback'),
                            array('size' => individualfeedback_ITEM_LABEL_TEXTBOX_SIZE,
                                  'maxlength' => 255));

        $mform->addElement('select',
                            'subtype',
                            get_string('multichoicetype', 'individualfeedback').'&nbsp;',
                            array('r'=>get_string('radio', 'individualfeedback'),
                                  'c'=>get_string('check', 'individualfeedback'),
                                  'd'=>get_string('dropdown', 'individualfeedback')));

        $mform->addElement('select',
                            'horizontal',
                            get_string('adjustment', 'individualfeedback').'&nbsp;',
                            array(0 => get_string('vertical', 'individualfeedback'),
                                  1 => get_string('horizontal', 'individualfeedback')));
        $mform->disabledIf('horizontal', 'subtype', 'eq', 'd');

        $mform->addElement('selectyesno',
                           'hidenoselect',
                           get_string('hide_no_select_option', 'individualfeedback'));
        $mform->disabledIf('hidenoselect', 'subtype', 'ne', 'r');

        $mform->addElement('selectyesno',
                           'ignoreempty',
                           get_string('do_not_analyse_empty_submits', 'individualfeedback'));

        $mform->addElement('textarea', 'values', get_string('multichoice_values', 'individualfeedback'),
            'wrap="virtual" rows="10" cols="65"');

        $mform->addElement('static', 'hint', '', get_string('use_one_line_for_each_value', 'individualfeedback'));

        parent::definition();
        $this->set_data($item);

    }

    public function set_data($item) {
        $info = $this->_customdata['info'];

        $item->horizontal = $info->horizontal;

        $item->subtype = $info->subtype;

        $itemvalues = str_replace(individualfeedback_MULTICHOICE_LINE_SEP, "\n", $info->presentation);
        $itemvalues = str_replace("\n\n", "\n", $itemvalues);
        $item->values = $itemvalues;

        return parent::set_data($item);
    }

    public function get_data() {
        if (!$item = parent::get_data()) {
            return false;
        }

        $presentation = str_replace("\n", individualfeedback_MULTICHOICE_LINE_SEP, trim($item->values));
        if (!isset($item->subtype)) {
            $subtype = 'r';
        } else {
            $subtype = substr($item->subtype, 0, 1);
        }
        if (isset($item->horizontal) AND $item->horizontal == 1 AND $subtype != 'd') {
            $presentation .= individualfeedback_MULTICHOICE_ADJUST_SEP.'1';
        }
        if (!isset($item->hidenoselect)) {
            $item->hidenoselect = 1;
        }
        if (!isset($item->ignoreempty)) {
            $item->ignoreempty = 0;
        }

        $item->presentation = $subtype.individualfeedback_MULTICHOICE_TYPE_SEP.$presentation;
        return $item;
    }
}
