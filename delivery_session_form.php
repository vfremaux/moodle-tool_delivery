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

defined('MOODLE_INTERNAL') || die;

/**
 * @package    tool_delivery
 * @category   tool
 * @author     Valery Fremaux <valery.fremaux@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once $CFG->libdir.'/formslib.php';

class Delivery_Session_Form extends moodleform{

    function definition() {
        global $CFG;

        $mform = $this->_form;

        $mform->addElement('header', 'head1', get_string($this->_customdata['mode'].'session', 'tool_delivery'));

        $mform->addElement('hidden', 'what', $this->_customdata['mode']);
        $mform->setType('what', PARAM_ALPHA);

        $mform->addElement('text', 'title', get_string('sessiontitle', 'tool_delivery'), array('size' => 80));
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', get_string('required'), 'required', null, 'client');

        $mform->addElement('textarea', 'description', get_string('description'), array('rows' => 10, 'cols' => 80));
        $mform->setType('description', PARAM_TEXT);

        if ($this->_customdata['mode'] != 'open') {
            $mform->addElement('textarea', 'impact', get_string('impact', 'tool_delivery'), array('rows' => 5, 'cols' => 80));
            $mform->setType('impact', PARAM_TEXT);

            $mform->addElement('textarea', 'outputstate', get_string('outputstatus', 'tool_delivery'), array('rows' => 5, 'cols' => 80));
            $mform->setType('outputstatus', PARAM_TEXT);

            $mform->addElement('textarea', 'followup', get_string('followup', 'tool_delivery'), array('rows' => 5, 'cols' => 80));
            $mform->setType('followup', PARAM_TEXT);
        }

        $group = array();
        if ($this->_customdata['mode'] == 'close') {
            $group[] = &$mform->createElement('submit', 'saveandclose', get_string('closesession', 'tool_delivery'));
        }
        $group[] = &$mform->createElement('submit', 'save', get_string('savesessiondata', 'tool_delivery'));
        $group[] = &$mform->createElement('cancel', 'cancel');

        $mform->addGroup($group, 'actionbuttons', '', false, false);
    }

    function validation($data, $files = array()) {
    }
}