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

require_once $CFG->libdir.'/formslib.php';

class Delivery_Form extends moodleform {

    function definition() {
        global $CFG;

        $mform = $this->_form;
        
        $config = get_config('tool_delivery');

        if (is_dir($config->prodscriptpath.'/'.$config->dir.'-'.strtoupper($config->method))) {
            $state = 'delivery';
            $prodcmd = 'cmd1';
            $delivcmd = 'cmd';
            $deliveryoptions = array();
            $prodoptions = array('disabled' => true);
        } else {
            $state = 'prod';
            $prodcmd = 'cmd';
            $delivcmd = 'cmd1';
            $deliveryoptions = array('disabled' => true);
            $prodoptions = array();
        }


        $mform->addElement('header', 'h1', '');
        $mform->setExpanded('h1');
        $mform->addElement('radio', $prodcmd, '', get_string('syncback', 'tool_delivery'), 'syncback', $prodoptions);
        $mform->addElement('radio', $prodcmd, '', get_string('supersyncback', 'tool_delivery'), 'supersyncback', $prodoptions);

        $mform->addElement('text', 'synccomponentpath', get_string('componentpath', 'tool_delivery'), array('size' => 40));
        $mform->setType('synccomponentpath', PARAM_PATH);

        $mform->addElement('header', 'h2', '');
        $mform->setExpanded('h2');
        $mform->addElement('radio', $prodcmd, '', get_string('goback', 'tool_delivery'), 'goback', $prodoptions);

        $mform->addElement('header', 'h3', '');
        $mform->setExpanded('h3');
        $mform->addElement('radio', $delivcmd, '', get_string('update', 'tool_delivery'), 'update', $deliveryoptions);

        $mform->addElement('text', 'componentpath', get_string('componentpath', 'tool_delivery'), array('size' => 40));
        $mform->setType('componentpath', PARAM_PATH);

        $mform->addElement('textarea', 'comment', get_string('comment', 'tool_delivery'), array('cols' => 40, 'rows' => 4));
        $mform->setType('comment', PARAM_TEXT);

        $mform->addElement('header', 'h4', '');
        $mform->setExpanded('h4');
        $mform->addElement('radio', $delivcmd, '', get_string('backtoprod', 'tool_delivery'), 'backtoprod', $deliveryoptions);

        $this->add_action_buttons(true, get_string('run', 'tool_delivery'));
    }

    function validation($data, $files = array()) {
    }
}