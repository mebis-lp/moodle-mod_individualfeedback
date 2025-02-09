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
 * Contains class mod_individualfeedback_complete_form
 *
 * @package   mod_individualfeedback
 * @copyright 2016 Marina Glancy
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Class mod_individualfeedback_complete_form
 *
 * @package   mod_individualfeedback
 * @copyright 2016 Marina Glancy
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_individualfeedback_complete_form extends moodleform {

    /** @var int */
    const MODE_COMPLETE = 1;
    /** @var int */
    const MODE_PRINT = 2;
    /** @var int */
    const MODE_EDIT = 3;
    /** @var int */
    const MODE_VIEW_RESPONSE = 4;
    /** @var int */
    const MODE_VIEW_TEMPLATE = 5;

    /** @var int */
    protected $mode;
    /** @var mod_individualfeedback_structure|mod_individualfeedback_completion */
    protected $structure;
    /** @var mod_individualfeedback_completion */
    protected $completion;
    /** @var int */
    protected $gopage;
    /** @var bool */
    protected $hasrequired = false;

    /**
     * Constructor
     *
     * @param int $mode
     * @param mod_individualfeedback_structure $structure
     * @param string $formid CSS id attribute of the form
     * @param array $customdata
     */
    public function __construct($mode, mod_individualfeedback_structure $structure, $formid, $customdata = null) {
        $this->mode = $mode;
        $this->structure = $structure;
        $this->gopage = isset($customdata['gopage']) ? $customdata['gopage'] : 0;
        $isanonymous = $this->structure->is_anonymous() ? ' ianonymous' : '';
        parent::__construct(null, $customdata, 'POST', '',
                array('id' => $formid, 'class' => 'individualfeedback_form' . $isanonymous), true);
    }

    /**
     * Form definition
     */
    public function definition() {
        $mform = $this->_form;
        $mform->addElement('hidden', 'id', $this->get_cm()->id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'courseid', $this->get_current_course_id());
        $mform->setType('courseid', PARAM_INT);
        $mform->addElement('hidden', 'gopage');
        $mform->setType('gopage', PARAM_INT);
        $mform->addElement('hidden', 'lastpage');
        $mform->setType('lastpage', PARAM_INT);
        $mform->addElement('hidden', 'startitempos');
        $mform->setType('startitempos', PARAM_INT);
        $mform->addElement('hidden', 'lastitempos');
        $mform->setType('lastitempos', PARAM_INT);

        // Add buttons to go to previous/next pages and submit the individualfeedback.
        if ($this->mode == self::MODE_COMPLETE) {
            $buttonarray = array();
            $buttonarray[] = &$mform->createElement('submit', 'gopreviouspage', get_string('previous_page', 'individualfeedback'));
            $buttonarray[] = &$mform->createElement('submit', 'gonextpage', get_string('next_page', 'individualfeedback'),
                    array('class' => 'form-submit'));
            $buttonarray[] = &$mform->createElement('submit', 'savevalues', get_string('save_entries', 'individualfeedback'),
                    array('class' => 'form-submit'));
            $buttonarray[] = &$mform->createElement('cancel');
            $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
            $mform->closeHeaderBefore('buttonar');
        }

        if ($this->mode == self::MODE_COMPLETE) {
            $this->definition_complete();
        } else {
            $this->definition_preview();
        }

        // Set data.
        $this->set_data(array('gopage' => $this->gopage));
    }

    /**
     * Called from definition_after_data() in the completion mode
     *
     * This will add only items from a current page to the individualfeedback and adjust the buttons
     */
    protected function definition_complete() {
        if (!$this->structure instanceof mod_individualfeedback_completion) {
            // We should not really be here but just in case.
            return;
        }
        $pages = $this->structure->get_pages();
        $gopage = $this->gopage;
        $pageitems = $pages[$gopage];
        $hasnextpage = $gopage < count($pages) - 1; // Until we complete this page we can not trust get_next_page().
        $hasprevpage = $gopage && ($this->structure->get_previous_page($gopage, false) !== null);

        // Add elements.
        foreach ($pageitems as $item) {
            $itemobj = individualfeedback_get_item_class($item->typ);
            $itemobj->complete_form_element($item, $this);
        }

        // Remove invalid buttons (for example, no "previous page" if we are on the first page).
        if (!$hasprevpage) {
            $this->remove_button('gopreviouspage');
        }
        if (!$hasnextpage) {
            $this->remove_button('gonextpage');
        }
        if ($hasnextpage) {
            $this->remove_button('savevalues');
        }
    }

    /**
     * Called from definition_after_data() in all modes except for completion
     *
     * This will add all items to the form, including pagebreaks as horizontal rules.
     */
    protected function definition_preview() {
        foreach ($this->structure->get_items() as $individualfeedbackitem) {
            $itemobj = individualfeedback_get_item_class($individualfeedbackitem->typ);

            // Add checkboxes in template view for selecting which questions to import.
            if ($this->get_mode() == self::MODE_VIEW_TEMPLATE) {
                $params = array('type' => 'checkbox', 'name' => 'question_' . $individualfeedbackitem->id,
                                    'data-id' => $individualfeedbackitem->id, 'data-typ' => $individualfeedbackitem->typ,
                                    'data-dependitem' => $individualfeedbackitem->dependitem,
                                    'class' => 'questionselect', 'checked' => 'checked');
                if ($individualfeedbackitem->typ == 'questiongroupend') {
                    $params['disabled'] = 'disabled';
                }
                $checkbox = html_writer::tag('input', '', $params);
                $this->add_form_element($individualfeedbackitem, ['html', $checkbox]);
            }

            $itemobj->complete_form_element($individualfeedbackitem, $this);
        }
    }

    /**
     * Removes the button that is not applicable for the current page
     *
     * @param string $buttonname
     */
    private function remove_button($buttonname) {
        $el = $this->_form->getElement('buttonar');
        foreach ($el->_elements as $idx => $button) {
            if ($button instanceof MoodleQuickForm_submit && $button->getName() === $buttonname) {
                unset($el->_elements[$idx]);
                return;
            }
        }
    }

    /**
     * Returns value for this element that is already stored in temporary or permanent table,
     * usually only available when user clicked "Previous page". Null means no value is stored.
     *
     * @param stdClass $item
     * @return string
     */
    public function get_item_value($item) {
        if ($this->structure instanceof mod_individualfeedback_completion) {
            return $this->structure->get_item_value($item);
        }
        return null;
    }

    /**
     * Can be used by the items to get the course id for which individualfeedback is taken
     *
     * This function returns 0 for individualfeedbacks that are located inside the courses.
     * $this->get_individualfeedback()->course will return the course where individualfeedback is located.
     * $this->get_current_course_id() will return the course where user was before taking the individualfeedback
     *
     * @return int
     */
    public function get_course_id() {
        return $this->structure->get_courseid();
    }

    /**
     * Record from 'individualfeedback' table corresponding to the current individualfeedback
     * @return stdClass
     */
    public function get_individualfeedback() {
        return $this->structure->get_individualfeedback();
    }

    /**
     * Current individualfeedback mode, see constants on the top of this class
     * @return int
     */
    public function get_mode() {
        return $this->mode;
    }

    /**
     * Returns whether the form is frozen, some items may prefer to change the element
     * type in case of frozen form. For example, text or textarea element does not look
     * nice when frozen
     *
     * @return bool
     */
    public function is_frozen() {
        return $this->mode == self::MODE_VIEW_RESPONSE;
    }

    /**
     * Returns the current course module
     * @return cm_info
     */
    public function get_cm() {
        return $this->structure->get_cm();
    }

    /**
     * Returns the course where user was before taking the individualfeedback.
     *
     * For individualfeedbacks inside the course it will be the same as $this->get_individualfeedback()->course.
     * For individualfeedbacks on the frontpage it will be the same as $this->get_course_id()
     *
     * @return int
     */
    public function get_current_course_id() {
        return $this->structure->get_courseid() ?: $this->get_individualfeedback()->course;
    }

    /**
     * CSS class for the item
     * @param stdClass $item
     * @return string
     */
    protected function get_suggested_class($item) {
        $class = "individualfeedback_itemlist individualfeedback-item-{$item->typ} questiongroupmoveitem";
        if ($item->dependitem) {
            if ($item->typ != 'questiongroupend') {
                $class .= " individualfeedback_is_dependent";
            }
        }
        if ($item->typ !== 'pagebreak') {
            $itemobj = individualfeedback_get_item_class($item->typ);
            if ($itemobj->get_hasvalue()) {
                $class .= " individualfeedback_hasvalue";
            }
        }
        return $class;
    }

    /**
     * Adds an element to this form - to be used by items in their complete_form_element() method
     *
     * @param stdClass $item
     * @param HTML_QuickForm_element|array $element either completed form element or an array that
     *      can be passed as arguments to $this->_form->createElement() function
     * @param bool $addrequiredrule automatically add 'required' rule
     * @param bool $setdefaultvalue automatically set default value for element
     * @return HTML_QuickForm_element
     */
    public function add_form_element($item, $element, $addrequiredrule = true, $setdefaultvalue = true) {
        global $OUTPUT;

        // Bsckport of MDL-62947 mod_feedback: fix feedback so it correctly uses forms API
        if (is_array($element) && $element[0] == 'group') {
            // For groups, use the mforms addGroup API.
            // $element looks like: ['group', $groupinputname, $name, $objects, $separator, $appendname],
            $element = $this->_form->addGroup($element[3], $element[1], $element[2], $element[4], $element[5]);
        } else {
            // Add non-group element to the form.
            if (is_array($element)) {
                if ($this->is_frozen() && $element[0] === 'text') {
                    // Convert 'text' element to 'static' when freezing for better display.
                    $element = ['static', $element[1], $element[2]];
                }
                $element = call_user_func_array(array($this->_form, 'createElement'), $element);
            }
            $element = $this->_form->addElement($element);
        }

        // Prepend standard CSS classes to the element classes.
        $attributes = $element->getAttributes();
        $class = !empty($attributes['class']) ? ' ' . $attributes['class'] : '';
        $attributes['class'] = $this->get_suggested_class($item) . $class;
        $element->setAttributes($attributes);

        // Add required rule.
        if ($item->required && $addrequiredrule) {
            $this->_form->addRule($element->getName(), get_string('required'), 'required', null, 'client');
        }

        // Set default value.
        if ($setdefaultvalue && ($tmpvalue = $this->get_item_value($item))) {
            $this->_form->setDefault($element->getName(), $tmpvalue);
        }

        // Freeze if needed.
        if ($this->is_frozen()) {
            $element->freeze();
        }

        // Add red asterisks on required fields.
        if ($item->required) {
            $required = $OUTPUT->pix_icon('req', get_string('requiredelement', 'form'));
            $element->setLabel($element->getLabel() . $required);
            $this->hasrequired = true;
        }

        // Add different useful stuff to the question name.
        $this->add_item_label($item, $element);
        $this->add_item_dependencies($item, $element);
        $this->add_item_number($item, $element);

        if ($this->mode == self::MODE_EDIT) {
            $this->enhance_name_for_edit($item, $element);
        }

        return $element;
    }

    /**
     * Adds a dummy element to this form - to be used by items in their complete_form_element() method
     *
     * @param stdClass $item
     * @param HTML_QuickForm_element|array $element either completed form element or an array that
     *      can be passed as arguments to $this->_form->createElement() function
     * @return HTML_QuickForm_element
     */
    public function add_dumy_form_element($item, $element) {
        global $OUTPUT;
        // Add element to the form.
        if (is_array($element)) {
            if ($this->is_frozen() && $element[0] === 'text') {
                // Convert 'text' element to 'static' when freezing for better display.
                $element = ['static', $element[1], $element[2]];
            }
            $element = call_user_func_array(array($this->_form, 'createElement'), $element);
        }
        $element = $this->_form->addElement($element);

        // Prepend standard CSS classes to the element classes.
        $attributes = $element->getAttributes();
        $class = !empty($attributes['class']) ? ' ' . $attributes['class'] : '';
        $attributes['class'] = 'dummy hidden' . $class;
        $element->setAttributes($attributes);

        // Freeze if needed.
        if ($this->is_frozen()) {
            $element->freeze();
        }

        return $element;
    }

    /**
     * Adds a group element to this form - to be used by items in their complete_form_element() method
     *
     * @param stdClass $item
     * @param string $groupinputname name for the form element
     * @param string $name question text
     * @param array $elements array of arrays that can be passed to $this->_form->createElement()
     * @param string $separator separator between group elements
     * @param string $class additional CSS classes for the form element
     * @return HTML_QuickForm_element
     */
    public function add_form_group_element($item, $groupinputname, $name, $elements, $separator,
            $class = '') {
        $objects = array();
        foreach ($elements as $element) {
            $object = call_user_func_array(array($this->_form, 'createElement'), $element);
            $objects[] = $object;
        }
        $element = $this->add_form_element($item,
                ['group', $groupinputname, $name, $objects, $separator, false],
                false,
                false);
        if ($class !== '') {
            $attributes = $element->getAttributes();
            $attributes['class'] .= ' ' . $class;
            $element->setAttributes($attributes);
        }
        return $element;
    }

    /**
     * Adds an item number to the question name (if individualfeedback autonumbering is on)
     * @param stdClass $item
     * @param HTML_QuickForm_element $element
     */
    protected function add_item_number($item, $element) {
        if ($this->get_individualfeedback()->autonumbering && !empty($item->itemnr)) {
            $name = $element->getLabel();
            $element->setLabel(html_writer::span($item->itemnr. '.', 'itemnr') . ' ' . $name);
        }
    }

    /**
     * Adds an item label to the question name
     * @param stdClass $item
     * @param HTML_QuickForm_element $element
     */
    protected function add_item_label($item, $element) {
        if (strlen($item->label) && ($this->mode == self::MODE_EDIT || $this->mode == self::MODE_VIEW_TEMPLATE)) {
            $name = $element->getLabel();
            $name = '('.format_string($item->label).') '.$name;
            $element->setLabel($name);
        }
    }

    /**
     * Adds a dependency description to the question name
     * @param stdClass $item
     * @param HTML_QuickForm_element $element
     */
    protected function add_item_dependencies($item, $element) {
        $allitems = $this->structure->get_items();
        if ($item->dependitem && ($this->mode == self::MODE_EDIT || $this->mode == self::MODE_VIEW_TEMPLATE)) {
            if (isset($allitems[$item->dependitem])) {
                $dependitem = $allitems[$item->dependitem];
                if ($dependitem->typ == 'questiongroup') {
                    $element->setLabel('');
                } else {
                    $name = $element->getLabel();
                    $name .= html_writer::span(' ('.format_string($dependitem->label).'-&gt;'.$item->dependvalue.')',
                            'individualfeedback_depend');
                    $element->setLabel($name);
                }
            }
        }
    }

    /**
     * Returns the CSS id attribute that will be assigned by moodleform later to this element
     * @param stdClass $item
     * @param HTML_QuickForm_element $element
     */
    protected function guess_element_id($item, $element) {
        if (!$id = $element->getAttribute('id')) {
            $attributes = $element->getAttributes();
            $id = $attributes['id'] = 'individualfeedback_item_' . $item->id;
            $element->setAttributes($attributes);
        }
        if ($element->getType() === 'group') {
            return 'fgroup_' . $id;
        }
        return 'fitem_' . $id;
    }

    /**
     * Adds editing actions to the question name in the edit mode
     * @param stdClass $item
     * @param HTML_QuickForm_element $element
     */
    protected function enhance_name_for_edit($item, $element) {
        global $OUTPUT;
        if ($item->typ == 'questiongroupend') {
            return;
        }

        $menu = new action_menu();
        $menu->set_owner_selector('#' . $this->guess_element_id($item, $element));
        $menu->set_constraint('.individualfeedback_form');
        $menu->set_alignment(action_menu::TR, action_menu::BR);
        $menu->set_menu_trigger(get_string('edit'));
        $menu->prioritise = true;

        $itemobj = individualfeedback_get_item_class($item->typ);
        $actions = $itemobj->edit_actions($item, $this->get_individualfeedback(), $this->get_cm());
        foreach ($actions as $action) {
            $menu->add($action);
        }
        $editmenu = $OUTPUT->render($menu);

        $name = $element->getLabel();

        $name = html_writer::span('', 'itemdd', array('id' => 'individualfeedback_item_box_' . $item->id)) .
                html_writer::span($name, 'itemname') .
                html_writer::span($editmenu, 'itemactions');
        $element->setLabel(html_writer::span($name, 'itemtitle'));
    }

    /**
     * Sets the default value for form element - alias to $this->_form->setDefault()
     * @param HTML_QuickForm_element|string $element
     * @param mixed $defaultvalue
     */
    public function set_element_default($element, $defaultvalue) {
        if ($element instanceof HTML_QuickForm_element) {
            $element = $element->getName();
        }
        $this->_form->setDefault($element, $defaultvalue);
    }


    /**
     * Sets the default value for form element - wrapper to $this->_form->setType()
     * @param HTML_QuickForm_element|string $element
     * @param int $type
     */
    public function set_element_type($element, $type) {
        if ($element instanceof HTML_QuickForm_element) {
            $element = $element->getName();
        }
        $this->_form->setType($element, $type);
    }

    /**
     * Adds a validation rule for the given field - wrapper for $this->_form->addRule()
     *
     * Do not use for 'required' rule!
     * Required * will be added automatically, if additional validation is needed
     * use method {@link self::add_validation_rule()}
     *
     * @param string $element Form element name
     * @param string $message Message to display for invalid data
     * @param string $type Rule type, use getRegisteredRules() to get types
     * @param string $format (optional)Required for extra rule data
     * @param string $validation (optional)Where to perform validation: "server", "client"
     * @param bool $reset Client-side validation: reset the form element to its original value if there is an error?
     * @param bool $force Force the rule to be applied, even if the target form element does not exist
     */
    public function add_element_rule($element, $message, $type, $format = null, $validation = 'server',
            $reset = false, $force = false) {
        if ($element instanceof HTML_QuickForm_element) {
            $element = $element->getName();
        }
        $this->_form->addRule($element, $message, $type, $format, $validation, $reset, $force);
    }

    /**
     * Adds a validation rule to the form
     *
     * @param callable $callback with arguments ($values, $files)
     */
    public function add_validation_rule(callable $callback) {
        if ($this->mode == self::MODE_COMPLETE) {
            $this->_form->addFormRule($callback);
        }
    }

    /**
     * Returns a reference to the element - wrapper for function $this->_form->getElement()
     *
     * @param string $elementname Element name
     * @return HTML_QuickForm_element reference to element
     */
    public function get_form_element($elementname) {
        return $this->_form->getElement($elementname);
    }

    /**
     * Displays the form
     */
    public function display() {
        global $OUTPUT, $PAGE;
        // Finalize the form definition if not yet done.
        if (!$this->_definition_finalized) {
            $this->_definition_finalized = true;
            $this->definition_after_data();
        }

        $mform = $this->_form;

        // Add "This form has required fields" text in the bottom of the form.
        if (($mform->_required || $this->hasrequired) &&
               ($this->mode == self::MODE_COMPLETE || $this->mode == self::MODE_PRINT || $this->mode == self::MODE_VIEW_TEMPLATE)) {
            $element = $mform->addElement('static', 'requiredfields', '',
                    get_string('somefieldsrequired', 'form',
                            $OUTPUT->pix_icon('req', get_string('requiredelement', 'form'))));
            $element->setAttributes($element->getAttributes() + ['class' => 'requirednote']);
        }

        // Reset _required array so the default red * are not displayed.
        $mform->_required = array();

        // Move buttons to the end of the form.
        if ($this->mode == self::MODE_COMPLETE) {
            $mform->addElement('hidden', '__dummyelement');
            $buttons = $mform->removeElement('buttonar', false);
            $mform->insertElementBefore($buttons, '__dummyelement');
            $mform->removeElement('__dummyelement');
        }

        $this->_form->display();

        if ($this->mode == self::MODE_EDIT) {
            $PAGE->requires->js_call_amd('mod_individualfeedback/edit', 'setup');
        }
    }
}
