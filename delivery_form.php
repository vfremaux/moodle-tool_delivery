<?php

require_once $CFG->libdir.'/formslib.php';

class Delivery_Form extends moodleform{

	function definition(){
		global $CFG;
	
		$mform = $this->_form;

		if (is_dir($CFG->tooldeliveryprodscriptpath.'/'.$CFG->tooldeliverydir.'-'.strtoupper($CFG->tooldeliverymethod))){
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
		$mform->addElement('radio', $prodcmd, '', get_string('syncback', 'tool_delivery'), 'syncback', $prodoptions);
		$mform->addElement('radio', $prodcmd, '', get_string('supersyncback', 'tool_delivery'), 'supersyncback', $prodoptions);

		$mform->addElement('text', 'synccomponentpath', get_string('componentpath', 'tool_delivery'), array('size' => 40));
		$mform->setType('synccomponentpath', PARAM_PATH);

		$mform->addElement('header', 'h2', '');
		$mform->addElement('radio', $prodcmd, '', get_string('goback', 'tool_delivery'), 'goback', $prodoptions);
		$mform->setExpanded('h2');

		$mform->addElement('header', 'h3', '');
		$mform->addElement('radio', $delivcmd, '', get_string('update', 'tool_delivery'), 'update', $deliveryoptions);
		$mform->setExpanded('h3');
		
		$mform->addElement('text', 'componentpath', get_string('componentpath', 'tool_delivery'), array('size' => 40));
		$mform->setType('componentpath', PARAM_PATH);

		$mform->addElement('textarea', 'comment', get_string('comment', 'tool_delivery'), array('cols' => 40, 'rows' => 4));
		$mform->setType('comment', PARAM_TEXT);

		$mform->addElement('header', 'h4', '');
		$mform->addElement('radio', $delivcmd, '', get_string('backtoprod', 'tool_delivery'), 'backtoprod', $deliveryoptions);
		$mform->setExpanded('h4');
		
		$this->add_action_buttons(true, get_string('run', 'tool_delivery'));
	}

	function validation($data, $files = array()){
	}
}